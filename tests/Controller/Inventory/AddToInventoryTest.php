<?php

namespace App\Tests\Controller\Inventory;

use App\Controller\InventoryController;
use App\Entity\Inventory;
use App\Entity\User;
use App\Repository\InventoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AddToInventoryTest extends TestCase
{
    public function testAddToInventorySuccess(): void{
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['itemName' => 'Trap', 'quantity' => 1, ]));
        $request ->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $user = new User();
        $user->setUsername('testuser');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $inventory = new Inventory();
        $inventory->setUsername('testuser');

        $inventoryRepo = $this->createMock(InventoryRepository::class);
        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($inventory);


        $controller = new InventoryController();
        $response = $controller->addToInventory($request, $em, $userRepo, $inventoryRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('inventory', $data);
        $this->assertEquals(1, $data['inventory']['trap']);
        $this->assertEquals(0, $data['inventory']['fake']);
        $this->assertEquals(0, $data['inventory']['poison']);
    }

    public function testAddToInventoryUserNotLoggedIn(): void{
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['itemName' => 'Trap', 'quantity' => 1, ]));
        $request ->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $user = new User();
        $user->setUsername('testuser');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $inventory = new Inventory();
        $inventory->setUsername('testuser');

        $inventoryRepo = $this->createMock(InventoryRepository::class);
        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($inventory);


        $controller = new InventoryController();
        $response = $controller->addToInventory($request, $em, $userRepo, $inventoryRepo);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('User not logged in', $data['message']);
    }

    public function testAddToInventoryInvalidQuantity(): void{
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['itemName' => 'Trap', 'quantity' => -1, ]));
        $request ->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $user = new User();
        $user->setUsername('testuser');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $inventory = new Inventory();
        $inventory->setUsername('testuser');

        $inventoryRepo = $this->createMock(InventoryRepository::class);
        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($inventory);


        $controller = new InventoryController();
        $response = $controller->addToInventory($request, $em, $userRepo, $inventoryRepo);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Invalid item data', $data['message']);
    }

    public function testAddToInventoryUserNotFound(): void{
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['itemName' => 'Trap', 'quantity' => 1, ]));
        $request ->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $user = new User();
        $user->setUsername('testuser');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);

        $inventory = new Inventory();
        $inventory->setUsername('testuser');

        $inventoryRepo = $this->createMock(InventoryRepository::class);
        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($inventory);


        $controller = new InventoryController();
        $response = $controller->addToInventory($request, $em, $userRepo, $inventoryRepo);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('User not found', $data['message']);
    }

    public function testAddToInventoryInventoryNotFound(): void{
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['itemName' => 'Trap', 'quantity' => 1, ]));
        $request ->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $user = new User();
        $user->setUsername('testuser');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $inventory = new Inventory();
        $inventory->setUsername('testuser');

        $inventoryRepo = $this->createMock(InventoryRepository::class);
        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);


        $controller = new InventoryController();
        $response = $controller->addToInventory($request, $em, $userRepo, $inventoryRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('inventory', $data);
        $this->assertEquals(1, $data['inventory']['trap']);
        $this->assertEquals(0, $data['inventory']['fake']);
        $this->assertEquals(0, $data['inventory']['poison']);
    }


    public function testAddToInventoryItemHasOtherNameButIsValid(): void{
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['itemName' => 'Fake hexagon', 'quantity' => 1, ]));
        $request ->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $user = new User();
        $user->setUsername('testuser');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $inventory = new Inventory();
        $inventory->setUsername('testuser');

        $inventoryRepo = $this->createMock(InventoryRepository::class);
        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);


        $controller = new InventoryController();
        $response = $controller->addToInventory($request, $em, $userRepo, $inventoryRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('inventory', $data);
        $this->assertEquals(0, $data['inventory']['trap']);
        $this->assertEquals(1, $data['inventory']['fake']);
        $this->assertEquals(0, $data['inventory']['poison']);
    }

    public function testAddToInventoryItemHasOtherNameButNotValid(): void{
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['itemName' => 'totally fake item', 'quantity' => 1, ]));
        $request ->setSession($session);

        $em = $this->createMock(EntityManagerInterface::class);

        $user = new User();
        $user->setUsername('testuser');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $inventory = new Inventory();
        $inventory->setUsername('testuser');

        $inventoryRepo = $this->createMock(InventoryRepository::class);
        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);


        $controller = new InventoryController();
        $response = $controller->addToInventory($request, $em, $userRepo, $inventoryRepo);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Invalid item type: totally fake item', $data['message']);
    }
}
