<?php

namespace App\Controller;
//ini_set('max_execution_time', 3000);
//ini_set('memory_limit', '1024M');
use App\Entity\Hexagon;
use App\Repository\HexagonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
}