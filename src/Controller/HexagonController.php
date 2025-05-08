<?php

namespace App\Controller;

use App\Entity\Hexagon;
use App\Repository\HexagonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HexagonController extends AbstractController
{
    #[Route('/insert-hexagons', methods: ['POST'])]
    public function insertHexagons(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        foreach ($data['hexagons'] as $hexData) {
            $hex = new Hexagon();
            $hex->setLatitude($hexData['latitude']);
            $hex->setLongitude($hexData['longitude']);
            $hex->setColor($hexData['color']);

            $em->persist($hex);
        }
        $em->flush();

        return new JsonResponse(['status' => 'success']);
    }
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
                'owner' => $hex->getOwner()
            ];
        }
        return new JsonResponse($data);
    }
}