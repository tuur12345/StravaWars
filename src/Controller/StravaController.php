<?php

namespace App\Controller;

use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaController extends AbstractController
{
    // create own Strava API application: https://www.strava.com/settings/api
    private $clientId = '153505'; // Replace with your Strava client ID
    private $clientSecret = '658ffd98156a5101444096565e9565a00c051ca4'; // Replace with your Strava client secret
    private $redirectUri = 'http://localhost:8080/strava/callback'; // redirect URL, dont change

    #[Route('/connect_strava', name:'connect_to_strava')]
    public function connect_strava(): Response { // start screen with button to connect to strava
        return $this->render('connect_strava.html.twig');
    }

    #[Route('/strava/connect', name:'strava_connect')]
    public function connect(): Response { // button calls this to redirect to strava login page
        $stravaAuthUrl = 'https://www.strava.com/oauth/authorize?' . http_build_query([
                'client_id' => $this->clientId,
                'response_type' => 'code',
                'redirect_uri' => $this->redirectUri,
                'scope' => 'read,read_all,profile:read_all,activity:read_all', // Get all the required scopes
                'state' => 'xyz', // Optional, can use for CSRF protection
            ]);

        return $this->redirect($stravaAuthUrl); // logs in with Strava profile
    }

    #[Route('/strava/callback', name:'strava_callback')]
    public function callback(Request $request, HttpClientInterface $httpClient): Response { // after login strava return to this
        if ($request->query->get('error') === 'access_denied') { // if users doesnt allow permission this should return them
            return $this->redirectToRoute('connect_to_strava');
        }

        $code = $request->query->get('code'); // get code from user

        if (!$code) {
            return $this->redirectToRoute('connect_to_strava'); // go back to start screen
        }

        try { // Make a request to exchange the code for an access token
            $response = $httpClient->request('POST', 'https://www.strava.com/oauth/token', [
                'json' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                ],
            ]);
            $data = $response->toArray();
        } catch (RequestException $e) {
            return $this->redirectToRoute('connect_to_strava'); // if request fails go back
        }

        $accessToken = $data['access_token']; // Retrieve the access token from the response
        $request->getSession()->set('access_token', $accessToken); // store accessToken for later requests
        // Use the access token to retrieve the user's data
        $userDataResponse = $httpClient->request('GET', 'https://www.strava.com/api/v3/athlete', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $userData = $userDataResponse->toArray();
        $request->getSession()->set('userData', $userData); // store user data in session for other pages

        return $this->redirectToRoute('home'); // go to the home screen
    }

    #[Route('/', name:'home')]
    public function home(Request $request, HttpClientInterface $httpClient): Response {
        $accessToken = $request->getSession()->get('access_token'); // retrieve accesstoken from session
        if (!$accessToken) {
            return $this->redirectToRoute('connect_to_strava'); // send back if no accesstoken
        }
        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }

        // get all the users activities
        $activitiesResponse = $httpClient->request('GET', 'https://www.strava.com/api/v3/athlete/activities', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'query' => [
                'per_page' => 3,
                'page' => 1,
            ],
        ]);

        $activities = $activitiesResponse->toArray();


        // Bereken de start van de week (bijvoorbeeld afgelopen zondag)
        $startOfWeek = strtotime('last sunday');
        $endOfWeek = strtotime('next sunday');

        // Filter de activiteiten die deze week zijn
        $weekActivities = array_filter($activities, function ($activity) use ($startOfWeek, $endOfWeek) {
            $activityDate = strtotime($activity['start_date']);
            // Controleer of de activiteit binnen de huidige week valt
            return $activityDate >= $startOfWeek && $activityDate < $endOfWeek;
        });
        // Tel de kudos van de activiteiten van deze week
        $totalKudosThisWeek = 0;

        foreach ($activities as $activity) {
            // Voeg de kudos van deze activiteit toe aan de totaalscore
            $totalKudosThisWeek += $activity['kudos_count'];
        }
        $request->getSession()->set('weekActivities', $activities); // sla de activiteiten van deze week op

        // Je kunt de activiteiten hier opslaan of naar de view sturen
        $request->getSession()->set('weekActivities', $weekActivities); // sla de activiteiten van deze week op
        $request->getSession()->set('totalKudosThisWeek', $totalKudosThisWeek);
        return $this->render('home.html.twig', [
            'weekActivities'=> $weekActivities,
            'activities' => $activities,
            'user' => $user,
            'totalKudosThisWeek'=> $totalKudosThisWeek
        ]);
    }

    #[Route('/maps', name:'maps')]
    public function maps(Request $request): Response {
        $weekActivities = $request->getSession()->get('weekActivities', []);
        $activities = $request->getSession()->get('activities', []);
        $totalKudosThisWeek = $request->getSession()->get('totalKudosThisWeek', 0);
        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }
        return $this->render('maps.html.twig', [
            'user' => $user,
            'weekActivities'=> $weekActivities,
            'activities'=> $activities,
            'totalKudosThisWeek'=> $totalKudosThisWeek
        ]);
    }

    #[Route('/profile', name:'profile')]
    public function profile(Request $request): Response {
        $user = $request->getSession()->get('userData'); // get user data from session
        $activities = $request->getSession()->get('activities', []);
        $totalKudosThisWeek = $request->getSession()->get('totalKudosThisWeek', 0);
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }
        return $this->render('profile.html.twig',
            [
                'user' => $user,
                'activities'=> $activities,
                'totalKudosThisWeek'=> $totalKudosThisWeek
            ]
        );
    }
}


