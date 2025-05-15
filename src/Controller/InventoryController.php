<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InventoryController extends AbstractController
{
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
        $userData = $request->getSession()->get('userData'); // get user data from session
        $user = $userRepository->findOneBy(['id' => $userData['id']]);
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

        $userData = $request->getSession()->get('userData'); // get user data from session
        $user = $userRepository->findOneBy(['id' => $userData['id']]);
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


}
