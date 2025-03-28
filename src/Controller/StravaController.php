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
    private $clientId = '153511'; // Replace with your Strava client ID
    private $clientSecret = '5744714c59aa271d85bcc43727c1ecebdc4bb4f3'; // Replace with your Strava client secret
    private $redirectUri = 'http://localhost:8080/strava/callback'; // redirect URL, dont change

    #[Route('/', name:'connect_to_strava')]
    public function connect_strava(): Response { // start screen with button to connect to strava
        return $this->render('connect_strava.html.twig');
    }

    #[Route('/strava/connect', name:'strava_connect')]
    public function connect(): Response { // button calls this to redirect to strava login page
        $stravaAuthUrl = 'https://www.strava.com/oauth/authorize?' . http_build_query([
                'client_id' => $this->clientId,
                'response_type' => 'code',
                'redirect_uri' => $this->redirectUri,
                'scope' => 'read,activity:read', // Add any required scope
                'state' => 'xyz', // Optional, can use for CSRF protection
            ]);

        return $this->redirect($stravaAuthUrl); // logs in with Strava profile
    }

    #[Route('/strava/callback', name:'strava_callback')]
    public function callback(Request $request, HttpClientInterface $httpClient): Response { // after login strava return to this
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

    #[Route('/home', name:'home')]
    public function home(): Response {
        return $this->render('home.html.twig');
    }

    #[Route('/maps', name:'maps')]
    public function maps(): Response {
        return $this->render('maps.html.twig');
    }

    #[Route('/profile', name:'profile')]
    public function profile(Request $request): Response {
        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }
        return $this->render('profile.html.twig',
            [
                'user' => $user
            ]
        );
    }
}


