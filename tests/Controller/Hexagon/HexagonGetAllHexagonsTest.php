<?php

namespace App\Tests\Controller\Hexagon;

use App\Controller\HexagonController;
use App\Repository\HexagonRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class HexagonGetAllHexagonsTest extends TestCase
{
    public function testGetAllHexagons()
    {
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setColor('red');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->method('getLatitude')->willReturn('1.23');   // string
        $hexagon->method('getLongitude')->willReturn('4.56');  // string
        $hexagon->method('getColor')->willReturn('red');
        $hexagon->method('getOwner')->willReturn('user1');
        $hexagon->method('getLevel')->willReturn(2);

        $HexagonRepo = $this->createMock(HexagonRepository::class);
        $HexagonRepo->method('findAll')->willReturn([$hexagon]);


        $controller = new HexagonController();
        $response = $controller->getAllHexagons($HexagonRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('1.23', $data[0]['latitude']);
        $this->assertEquals('4.56', $data[0]['longitude']);
        $this->assertEquals('red', $data[0]['color']);
        $this->assertEquals('user1', $data[0]['owner']);
        $this->assertEquals(2, $data[0]['level']);
    }

}
