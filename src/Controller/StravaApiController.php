<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaApiController extends AbstractController
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
    public function callback(Request $request, HttpClientInterface $httpClient, EntityManagerInterface $entityManager, UserRepository $userRepository): Response { // after login strava return to this
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
        $request->getSession()->remove('kudos_converted_this_session');
        // Check if user exists in database, if not create a new user
        $user = $userRepository->findOneBy(['username' => $userData['username']]);

        $colors = ['blue', 'red', 'green', 'yellow'];

        if (!$user) {
            // Create new user
            $user = new User();
            $user->setUsername($userData['username']);
            $user->setColor($colors[rand(0, count($colors) - 1)]); // give user random color
            $user->setStravabucks(0); // Initialize coins to zero
            $entityManager->persist($user);
            $entityManager->flush();
        }
        $request->getSession()->set('strava_username', $user->getUsername());

        return $this->redirectToRoute('home'); // go to the home screen
    }
}
