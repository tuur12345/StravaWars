<?php

namespace App\Controller;

use App\Entity\Hexagon;
use App\Repository\HexagonRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\DBAL\Connection;

class StravaController extends AbstractController
{
    // create own Strava API application: https://www.strava.com/settings/api
    private $clientId = '153511'; // Replace with your Strava client ID
    private $clientSecret = '5744714c59aa271d85bcc43727c1ecebdc4bb4f3'; // Replace with your Strava client secret
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
    public function home(Request $request, HttpClientInterface $httpClient, HexagonRepository $hexagonRepository): Response {
        $accessToken = $request->getSession()->get('access_token'); // retrieve accesstoken from session
        if (!$accessToken) {
            return $this->redirectToRoute('connect_to_strava'); // send back if no accesstoken
        }
        $response = $httpClient->request('GET', 'https://www.strava.com/api/v3/athlete', [ // check if token is still valid
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
        if ($response->getStatusCode() === 401) { // Token is invalid or expired
            return $this->redirectToRoute('connect_to_strava');
        }

        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }

        // get three most recent users activities
        $activitiesResponse = $httpClient->request('GET', 'https://www.strava.com/api/v3/athlete/activities', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken
            ],
        ]);

        $activities = $activitiesResponse->toArray();


        // calc start of week
        $startOfWeek = strtotime('-6 week');
        $endOfWeek = strtotime('next sunday');

        // filter weekly activities
        $weekActivities = array_filter($activities, function ($activity) use ($startOfWeek, $endOfWeek) {
            $activityDate = strtotime($activity['start_date']);
            // check if activity within this week
            return $activityDate >= $startOfWeek && $activityDate < $endOfWeek;
        });
        //count kudos of this week activities
        $totalKudosThisWeek = 0;

        foreach ($weekActivities as $activity) {
            // add kudos to total
            $totalKudosThisWeek += $activity['kudos_count'];
        }

        $request->getSession()->set('weekActivities', $weekActivities); // save activities of this week
        $request->getSession()->set('totalKudosThisWeek', $totalKudosThisWeek); // save kudos

        $data = $hexagonRepository->findAll();
        $hexagons = [];
        foreach ($data as $hex) {
            $hexagons[] = [
                'latitude' => $hex->getLatitude(),
                'longitude' => $hex->getLongitude(),
                'color' => $hex->getColor(),
                'owner' => $hex->getOwner(),
                'level' => $hex->getLevel()
            ];
        }
        $request->getSession()->set('hexagons', $hexagons);

        return $this->render('home.html.twig', [
            'activities' => $weekActivities,
            'user' => $user,
            'totalKudosThisWeek'=> $totalKudosThisWeek,
            'Kudostocoins'=> round($totalKudosThisWeek/2),
            'hexagons' => $hexagons
        ]);
    }

    #[Route('/maps', name:'maps')]
    public function maps(Request $request, HexagonRepository $hexagonRepository): Response {
        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }

        $weekActivities = $request->getSession()->get('weekActivities', []);
        $totalKudosThisWeek = $request->getSession()->get('totalKudosThisWeek', 0);

        $hexagons = $request->getSession()->get('hexagons', []);

        return $this->render('maps.html.twig', [
            'user' => $user,
            'activities' => $weekActivities,
            'totalKudosThisWeek'=> $totalKudosThisWeek,
            'Kudostocoins'=> round($totalKudosThisWeek),
            'hexagons' => $hexagons
        ]);
    }

    #[Route('/profile', name:'profile')]
    public function profile(Request $request): Response {
        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }

        $weekActivities = $request->getSession()->get('weekActivities', []);
        $totalKudosThisWeek = $request->getSession()->get('totalKudosThisWeek', 0);

        return $this->render('profile.html.twig',
            [
                'user' => $user,
                'activities' => $weekActivities,
                'totalKudosThisWeek'=> $totalKudosThisWeek,

            ]
        );
    }
}


