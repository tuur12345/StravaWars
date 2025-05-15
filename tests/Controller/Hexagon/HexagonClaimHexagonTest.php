<?php

namespace App\Tests\Controller\Hexagon;

use App\Controller\HexagonController;
use App\Repository\HexagonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn('user2');

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => 'user2','level' => 1, 'color' => 'blue']));
        $request->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);
//        $em->expects($this->once())->method('flush');

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $user = new \App\Entity\User();
        $user->setUsername('user2')->setStravabucks(13);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'user2'])->willReturn($user);

        $controller = new HexagonController();
        $response = $controller->claimHexagonAction($request, $hexaRepo,$userRepo, $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('user2', $data['owner']);
        $this->assertEquals(1, $data['level']);
        $this->assertEquals('blue', $data['color']);
    }

    public function testClaimHexagonUserNotLoggedIn(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => 'user1','level' => 2, 'color' => 'red']));
        $request->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $user = new \App\Entity\User();
        $user->setUsername('user2')->setStravabucks(13);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'user2'])->willReturn($user);

        $controller = new HexagonController();
        $response = $controller->claimHexagonAction($request, $hexaRepo, $userRepo, $em);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Log in for this action.', $data['message']);
    }

    public function testClaimHexagonUserNotFound(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn('user2');

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => 'user1','level' => 2, 'color' => 'red']));
        $request->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $user = new \App\Entity\User();
        $user->setUsername('user2')->setStravabucks(13);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'user2'])->willReturn(null);

        $controller = new HexagonController();
        $response = $controller->claimHexagonAction($request, $hexaRepo, $userRepo, $em);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('User not found.', $data['message']);
    }

    public function testClaimHexagonUserHasNotEnoughStravabucks(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn('user2');

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => 'user1','level' => 2, 'color' => 'red']));
        $request->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $user = new \App\Entity\User();
        $user->setUsername('user2')->setStravabucks(0);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'user2'])->willReturn($user);

        $controller = new HexagonController();
        $response = $controller->claimHexagonAction($request, $hexaRepo, $userRepo, $em);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Not enough stravabucks. You need 1 Stravabuck(s) for this action.', $data['message']);
        $this->assertEquals(0, $data['current_balance']);
        $this->assertEquals(1, $data['cost']);
    }

    public function testClaimHexagonHexagonNotFound(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn('user2');

        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => 'user1','level' => 2, 'color' => 'red']));
        $request->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn(null);

        $user = new \App\Entity\User();
        $user->setUsername('user2')->setStravabucks(13);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'user2'])->willReturn($user);

        $controller = new HexagonController();
        $response = $controller->claimHexagonAction($request, $hexaRepo, $userRepo, $em);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Hexagon not found', $data['message']);
    }

    public function testClaimHexagonInvalidHexagon(): void{
        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
        $hexagon = new \App\Entity\Hexagon();
        $hexagon->setLatitude('1.23');
        $hexagon->setLongitude('4.56');
        $hexagon->setOwner('user1');
        $hexagon->setLevel(2);
        $hexagon->setColor('red');

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn('user2');

        $request = new Request([], [], [], [], [], [], json_encode([]));
        $request->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $hexaRepo = $this->createMock(HexagonRepository::class);
        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);

        $user = new \App\Entity\User();
        $user->setUsername('user2')->setStravabucks(13);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'user2'])->willReturn($user);

        $controller = new HexagonController();
        $response = $controller->claimHexagonAction($request, $hexaRepo, $userRepo, $em);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Invalid hexagon', $data['message']);
    }


//    public function testClaimHexagonZeroAndEmptyString(): void{
//        $hexagon = $this->createMock(\App\Entity\Hexagon::class);
//        $hexagon = new \App\Entity\Hexagon();
//        $hexagon->setLatitude('1.23');
//        $hexagon->setLongitude('4.56');
//        $hexagon->setOwner('user1');
//        $hexagon->setLevel(2);
//        $hexagon->setColor('red');
//
//        $request = new Request([], [], [], [], [], [], json_encode(['latitude' => '1.23', 'longitude' => '4.56','owner' => '','level' => 0, 'color' => '']));
//
//        $em = $this->createMock(EntityManagerInterface::class);
//        $em->expects($this->once())->method('flush');
//
//        $hexaRepo = $this->createMock(HexagonRepository::class);
//        $hexaRepo->method('findOneBy')->with(['latitude' => '1.23', 'longitude' => '4.56'])->willReturn($hexagon);
//
//        $controller = new HexagonController();
//        $response = $controller->claimHexagon($request, $hexaRepo, $em);
//
//        $this->assertInstanceOf(JsonResponse::class, $response);
//        $data = json_decode($response->getContent(), true);
//        $this->assertEquals('', $data['owner']);
//        $this->assertEquals(0, $data['level']);
//        $this->assertEquals('', $data['color']);
//    }
}
