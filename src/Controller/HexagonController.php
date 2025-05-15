<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HexagonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Hexagon;

class HexagonController extends AbstractController
{
    private const COST_PER_ACTION = 1;

    #[Route('/hexagons', name: 'getAllHexagons', methods:['GET'])]
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


    #[Route('/hexagon/claim', name: 'claimHexagon', methods: ['POST'])]
    public function claimHexagonAction(
        Request $request,
        HexagonRepository $hexagonRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {

        $userData = $request->getSession()->get('userData');
        $user = $userRepository->findOneBy(['id' => $userData['id']]);
        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'User not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentStravabucks = $user->getStravabucks();
        if ($currentStravabucks < self::COST_PER_ACTION) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Not enough stravabucks. You need ' . self::COST_PER_ACTION . ' Stravabuck for this action.',
                'current_balance' => $currentStravabucks,
                'cost' => self::COST_PER_ACTION
            ], Response::HTTP_FORBIDDEN);
        }

        $user->setStravabucks($currentStravabucks - self::COST_PER_ACTION);
        $em->persist($user);

        $hexagonData = json_decode($request->getContent(), true);
        if (!$hexagonData || !isset($hexagonData['latitude']) || !isset($hexagonData['longitude'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid hexagon'], Response::HTTP_BAD_REQUEST);
        }

        $latitude = $hexagonData['latitude'];
        $longitude = $hexagonData['longitude'];
        $newOwner = $hexagonData['owner'] ?? 'None';
        $newLevel = $hexagonData['level'] ?? 0;
        $newColor = $hexagonData['color'] ?? '#FFFFFF';

        $hexagonEntity = $hexagonRepository->findOneBy(['latitude' => $latitude, 'longitude' => $longitude]);

        if (!$hexagonEntity) {
            $em->remove($user);
            $em->flush();
            return new JsonResponse(['status' => 'error', 'message' => 'Hexagon not found'], Response::HTTP_NOT_FOUND);
        }


        $hexagonEntity->setOwner($newOwner);
        $hexagonEntity->setLevel($newLevel);
        $hexagonEntity->setColor($newColor);

        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'owner' => $hexagonEntity->getOwner(),
            'level' => $hexagonEntity->getLevel(),
            'color' => $hexagonEntity->getColor(),
            'new_stravabucks_balance' => $user->getStravabucks()
        ]);
    }
}