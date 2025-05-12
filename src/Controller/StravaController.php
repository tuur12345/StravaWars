<?php

namespace App\Controller;
//ini_set('max_execution_time', 3000);
//ini_set('memory_limit', '1024M');

use App\Entity\Hexagon;
use App\Entity\User;
use App\Entity\Inventory;
use App\Repository\HexagonRepository;
use App\Repository\InventoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\DBAL\Connection;

class StravaController extends AbstractController
{
    #[Route('/', name:'home')]
    public function home(Request $request, HttpClientInterface $httpClient, UserRepository $userRepository, HexagonRepository $hexagonRepository, InventoryRepository $inventoryRepository): Response {
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

        // Get user from database to display current stravabucks
        $stravaUsername = $request->getSession()->get('strava_username');
        $dbUser = $userRepository->findOneBy(['username' => $stravaUsername]);
        $stravabucks = $dbUser ? $dbUser->getStravabucks() : 0;

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
        $kudosAlreadyConvertedThisSession = $request->getSession()->get('kudos_converted_this_session', false);

        return $this->render('home.html.twig', [
            'activities' => $weekActivities,
            'user' => $user,
            'totalKudosThisWeek'=> $totalKudosThisWeek,
            'Kudostocoins'=> round($totalKudosThisWeek/2),
            'stravabucks' => $stravabucks,
            'hexagons' => $hexagons,
            'kudosAlreadyConverted' => $kudosAlreadyConvertedThisSession // Variabele voor Twig
        ]);
    }

    #[Route('/maps', name:'maps')]
    public function maps(Request $request, UserRepository $userRepository, HexagonRepository $hexagonRepository): Response {
        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }

        $weekActivities = $request->getSession()->get('weekActivities', []);
        $totalKudosThisWeek = $request->getSession()->get('totalKudosThisWeek', 0);

        // Get user's stravabucks
        $stravaUsername = $request->getSession()->get('strava_username');
        $dbUser = $userRepository->findOneBy(['username' => $stravaUsername]);
        $stravabucks = $dbUser ? $dbUser->getStravabucks() : 0;

        $hexagons = $request->getSession()->get('hexagons', []);
        $kudosAlreadyConvertedThisSession = $request->getSession()->get('kudos_converted_this_session', false);

        return $this->render('maps.html.twig', [
            'user' => $user,
            'activities' => $weekActivities,
            'totalKudosThisWeek'=> $totalKudosThisWeek,
            'Kudostocoins'=> round($totalKudosThisWeek/2),
            'stravabucks' => $stravabucks,
            'hexagons' => $hexagons,
            'kudosAlreadyConverted' => $kudosAlreadyConvertedThisSession // Variabele voor Twig
        ]);
    }

    #[Route('/profile', name:'profile')]
    public function profile(Request $request, UserRepository $userRepository): Response {
        $user = $request->getSession()->get('userData'); // get user data from session
        if (!$user) {
            return $this->redirectToRoute('connect_to_strava'); // if no user data go back to start screen
        }

        $weekActivities = $request->getSession()->get('weekActivities', []);
        $totalKudosThisWeek = $request->getSession()->get('totalKudosThisWeek', 0);

        // Get user's stravabucks
        $stravaUsername = $request->getSession()->get('strava_username');
        $dbUser = $userRepository->findOneBy(['username' => $stravaUsername]);
        $stravabucks = $dbUser ? $dbUser->getStravabucks() : 0;

        return $this->render('profile.html.twig',
            [
                'user' => $user,
                'activities' => $weekActivities,
                'totalKudosThisWeek'=> $totalKudosThisWeek,
                'stravabucks' => $stravabucks
            ]
        );
    }


}