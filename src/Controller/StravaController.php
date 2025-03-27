<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaController extends AbstractController
{
    private $clientId = '153511'; // Replace with your Strava client ID
    private $clientSecret = '5744714c59aa271d85bcc43727c1ecebdc4bb4f3'; // Replace with your Strava client secret
    private $redirectUri = 'http://localhost:8080/strava/callback'; // Replace with your actual redirect URI

    #[Route('/', name:'home')]
    public function home(): Response {
        return $this->render('base.html.twig');
    }

    #[Route('/strava/connect', name:'strava_connect')]
    public function connect(): Response {
        $stravaAuthUrl = 'https://www.strava.com/oauth/authorize?' . http_build_query([
                'client_id' => $this->clientId,
                'response_type' => 'code',
                'redirect_uri' => $this->redirectUri,
                'scope' => 'read,activity:read', // Add any required scope
                'state' => 'xyz', // Optional, can use for CSRF protection
            ]);

        return $this->redirect($stravaAuthUrl);
    }

    #[Route('/strava/callback', name:'strava_callback')]
    public function callback(Request $request, HttpClientInterface $httpClient): Response {
        $code = $request->query->get('code');
        if (!$code) {
            return $this->redirectToRoute('home');
        }

        // Make a request to exchange the code for an access token
        $response = $httpClient->request('POST', 'https://www.strava.com/oauth/token', [
            'json' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ],
        ]);

        $data = $response->toArray();

        // Retrieve the access token from the response
        $accessToken = $data['access_token'];

        // Use the access token to retrieve the user's data
        $userDataResponse = $httpClient->request('GET', 'https://www.strava.com/api/v3/athlete', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        $userData = $userDataResponse->toArray();

        // You can now store the user data or display it
        dd($userData);
    }
}


