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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\DBAL\Connection;

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

        // Check if user exists in database, if not create a new user
        $stravaUsername = $userData['username'] ?? $userData['firstname'] . '_' . $userData['id'];
        $user = $userRepository->findOneBy(['username' => $stravaUsername]);

        if (!$user) {
            // Create new user
            $user = new User();
            $user->setUsername($stravaUsername);
            $user->setStravabucks(0); // Initialize coins to zero
            $entityManager->persist($user);
            $entityManager->flush();
        }

        // Store username in session for easy access
        $request->getSession()->set('strava_username', $stravaUsername);

        return $this->redirectToRoute('home'); // go to the home screen
    }

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

        return $this->render('home.html.twig', [
            'activities' => $weekActivities,
            'user' => $user,
            'totalKudosThisWeek'=> $totalKudosThisWeek,
            'Kudostocoins'=> round($totalKudosThisWeek/2),
            'stravabucks' => $stravabucks,
            'hexagons' => $hexagons
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

        return $this->render('maps.html.twig', [
            'user' => $user,
            'activities' => $weekActivities,
            'totalKudosThisWeek'=> $totalKudosThisWeek,
            'Kudostocoins'=> round($totalKudosThisWeek/2),
            'stravabucks' => $stravabucks,
            'hexagons' => $hexagons
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
    #[Route('/add-to-inventory', name: 'add_to_inventory', methods: ['POST'])]
    public function addToInventory(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, InventoryRepository $inventoryRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $itemName = $data['itemName'] ?? null;
        $quantity = $data['quantity'] ?? 0;
        $stravaUsername = $request->getSession()->get('strava_username');

        if (!$stravaUsername) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not logged in'], 401);
        }

        if (!$itemName || $quantity <= 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid item data'], 400);
        }

        $user = $userRepository->findOneBy(['username' => $stravaUsername]);
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $inventory = $inventoryRepository->findOneBy(['username' => $stravaUsername]);
        if (!$inventory) {
            $inventory = new Inventory();
            $inventory->setUsername($stravaUsername);
            // Initialiseer items op 0 als dat nog niet gebeurt in de constructor van Inventory
            $inventory->setTrap(0);
            $inventory->setFake(0);
            $inventory->setPoison(0);
            $entityManager->persist($inventory);
        }

        // Gebruik de data-item attribuut waarde (lowercase) als itemType
        $itemType = strtolower($itemName); // Zorg ervoor dat de itemnaam overeenkomt met je entity

        // Valideer of itemType een geldige property is in Inventory om errors te voorkomen
        // Dit kan verbeterd worden door bijvoorbeeld een array van geldige item types te hebben
        if (!property_exists(Inventory::class, $itemType)) {
            // Probeer een mapping als de directe property niet bestaat (bv. "Fake Hexagon" -> "fake")
            $itemMapping = [
                'trap' => 'trap',
                'fake hexagon' => 'fake', // voorbeeld mapping
                'poison' => 'poison'
            ];
            if (!isset($itemMapping[$itemType])) {
                return new JsonResponse(['status' => 'error', 'message' => "Invalid item type: {$itemType}"], 400);
            }
            $itemType = $itemMapping[$itemType];
        }


        // Gebruik de bestaande addItem methode in je Inventory entity
        $inventory->addItem($itemType, $quantity);

        $entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => "{$quantity}x {$itemName} added to inventory.",
            'inventory' => [ // Stuur eventueel de bijgewerkte inventaris mee
                'trap' => $inventory->getTrap(),
                'fake' => $inventory->getFake(),
                'poison' => $inventory->getPoison(),
            ]
        ]);
    }

    #[Route('/get-inventory', name: 'get_inventory', methods: ['GET'])]
    public function getInventory(Request $request, InventoryRepository $inventoryRepository, UserRepository $userRepository): JsonResponse
    {
        $stravaUsername = $request->getSession()->get('strava_username');

        if (!$stravaUsername) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not logged in'], 401);
        }

        $user = $userRepository->findOneBy(['username' => $stravaUsername]);
        if (!$user) {
            // Dit zou niet moeten gebeuren als de user ingelogd is, maar voor de zekerheid
            return new JsonResponse(['status' => 'error', 'message' => 'User not found for inventory'], 404);
        }

        $inventory = $inventoryRepository->findOneBy(['username' => $stravaUsername]);

        if (!$inventory) {
            // Geef een lege inventaris terug als de gebruiker nog niks heeft
            return new JsonResponse([
                'status' => 'success',
                'inventory' => [
                    'trap' => 0,
                    'fake' => 0,
                    'poison' => 0,
                    // Voeg hier andere items toe als je die hebt, met een default van 0
                ]
            ]);
        }

        return new JsonResponse([
            'status' => 'success',
            'inventory' => [
                'trap' => $inventory->getTrap(),
                'fake' => $inventory->getFake(),
                'poison' => $inventory->getPoison(),
                // Voeg hier andere items toe als je die hebt
            ]
        ]);
    }


    #[Route('/add-stravabucks', name:'add_stravabucks', methods: ['POST'])]
    public function addStravabucks(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;
        $stravaUsername = $request->getSession()->get('strava_username');

        if (!$stravaUsername) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not logged in'], 401);
        }

        $user = $userRepository->findOneBy(['username' => $stravaUsername]);
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
        }

        // Add amount to current stravabucks
        $currentAmount = $user->getStravabucks();
        $user->setStravabucks($currentAmount + $amount);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Stravabucks added successfully',
            'current_balance' => $user->getStravabucks()
        ]);
    }

    #[Route('/use-stravabucks', name:'use_stravabucks', methods: ['POST'])]
    public function useStravabucks(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;
        $stravaUsername = $request->getSession()->get('strava_username');

        if (!$stravaUsername) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not logged in'], 401);
        }

        $user = $userRepository->findOneBy(['username' => $stravaUsername]);
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $currentAmount = $user->getStravabucks();

        // Check if user has enough stravabucks
        if ($currentAmount < $amount) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Not enough stravabucks',
                'current_balance' => $currentAmount
            ], 400);
        }

        // Deduct amount from current stravabucks
        $user->setStravabucks($currentAmount - $amount);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Purchase successful',
            'current_balance' => $user->getStravabucks()
        ]);
    }

    #[Route('/get-stravabucks', name:'get_stravabucks')]
    public function getStravabucks(Request $request, UserRepository $userRepository): JsonResponse
    {
        $stravaUsername = $request->getSession()->get('strava_username');

        if (!$stravaUsername) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not logged in'], 401);
        }

        $user = $userRepository->findOneBy(['username' => $stravaUsername]);
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
        }

        return new JsonResponse([
            'status' => 'success',
            'stravabucks' => $user->getStravabucks()
        ]);
    }
//    #[Route('/insert-hexagons', methods: ['POST'])]
//    public function insertHexagons(Request $request, EntityManagerInterface $em): JsonResponse
//    {
//        // insert hexagons in database in batches
//        $data = json_decode($request->getContent(), true);
//
//        $batchSize = 100;
//        $i = 0;
//
//        foreach ($data['hexagons'] as $hexData) {
//            $hex = new Hexagon();
//            $hex->setLatitude($hexData['latitude']);
//            $hex->setLongitude($hexData['longitude']);
//            $hex->setColor($hexData['color']);
//            $hex->setOwner($hexData['owner']);
//            $hex->setLevel($hexData['level']);
//
//            $em->persist($hex);
//            $i++;
//
//            if (($i % $batchSize) === 0) {
//                $em->flush();
//                $em->clear(); // free memory
//            }
//        }
//        $em->flush();
//        $em->clear();
//
//        return new JsonResponse(['status' => 'success']);
//    }

    #[Route('/hexagons', name: 'getAllHexagons')]
    public function getAllHexagons(HexagonRepository $hexagonRepository): JsonResponse
    {
        $hexagons = $hexagonRepository->findAll();
        $data = [];
        foreach ($hexagons as $hex) {
            $data[] = [
                'latitude' => $hex->getLatitude(),
                'longitude' => $hex->getLongitude(),
                'color' => $hex->getColor(),
                'owner' => $hex->getOwner(),
                'level' => $hex->getLevel()
            ];
        }
        return new JsonResponse($data);
    }

}