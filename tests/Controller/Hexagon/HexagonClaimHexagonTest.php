<?php

namespace App\Tests\Controller\Hexagon;

use App\Controller\HexagonController;
use App\Repository\HexagonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\TestCase;

class HexagonClaimHexagonTest extends TestCase
{
    public function testClaimHexagonDifferentValues(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => 'user2','level' => 1, 'color' => 'blue']));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $controller = new HexagonController();
        $response = $controller->claimHexagon($request, $hexaRepo, $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('user2', $data['owner']);
        $this->assertEquals(1, $data['level']);
        $this->assertEquals('blue', $data['color']);
    }

    public function testClaimHexagonSameValues(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => 'user1','level' => 2, 'color' => 'red']));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $controller = new HexagonController();
        $response = $controller->claimHexagon($request, $hexaRepo, $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('user1', $data['owner']);
        $this->assertEquals(2, $data['level']);
        $this->assertEquals('red', $data['color']);
    }

    public function testClaimHexagonZeroAndEmptyString(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => '','level' => 0, 'color' => '']));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $controller = new HexagonController();
        $response = $controller->claimHexagon($request, $hexaRepo, $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('', $data['owner']);
        $this->assertEquals(0, $data['level']);
        $this->assertEquals('', $data['color']);
    }
}
