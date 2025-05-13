<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StravabucksController extends AbstractController
{
    #[Route('/add-stravabucks', name:'add_stravabucks', methods: ['POST'])]
    public function addStravabucks(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        // SERVER-SIDE CHECK: Zijn kudos al geconverteerd in deze sessie?
        if ($request->getSession()->get('kudos_converted_this_session', false)) {
            $stravaUsername = $request->getSession()->get('strava_username');
            $user = $stravaUsername ? $userRepository->findOneBy(['username' => $stravaUsername]) : null;
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Kudos already converted in this session.',
                'current_balance' => $user ? $user->getStravabucks() : 0
            ], 403); // 403 Forbidden is een goede HTTP status hiervoor
        }
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
        $request->getSession()->set('kudos_converted_this_session', true);
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

}
