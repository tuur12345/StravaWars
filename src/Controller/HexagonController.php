<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\HexagonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HexagonController extends AbstractController
{
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
    #[Route('/hexagon/claim', name: 'claimHexagon')]
    public function claimHexagon(Request $request, HexagonRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $hexagon = json_decode($request->getContent(), true);
        $latitude = $hexagon['latitude'];
        $longitude = $hexagon['longitude'];
        $owner = $hexagon['owner'];
        $level = $hexagon['level'];
        $color = $hexagon['color'];

        $hexagon = $repo->findOneBy(['latitude' => $latitude, 'longitude' => $longitude]);

        $hexagon->setOwner($owner);
        $hexagon->setLevel($level);
        $hexagon->setColor($color);

        $em->flush();

        return new JsonResponse([
            'owner' => $hexagon->getOwner(),
            'level' => $hexagon->getLevel(),
            'color' => $hexagon->getColor(),
        ]);
    }
}
