<?php
//
//namespace App\Tests\Controller\Inventory;
//
//use App\Controller\InventoryController;
//use App\Entity\Inventory;
//use App\Entity\User;
//use App\Repository\InventoryRepository;
//use App\Repository\UserRepository;
//use Doctrine\ORM\EntityManagerInterface;
//use PHPUnit\Framework\TestCase;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
//
//class GetInventoryTest extends TestCase
//{
////    public function testGetInventorySuccess(): void
////    {
////        $session = $this->createMock(SessionInterface::class);
////        $session->method('get')->willReturnMap([
////            ['strava_username', 'testuser', 'testuser'],
////            ['userData', ['id' => 13], ['id' => 13]],
////        ]);
////
////
////        $request = new Request([], [], [], [], [], [], json_encode([]));
////        $request ->setSession($session);
////
////        $em = $this->createMock(EntityManagerInterface::class);
////
////        $user = new User();
////        $user->setId(13);
////
////        $userRepo = $this->createMock(UserRepository::class);
////        $userRepo->method('findOneBy')->with(['id' => 13])->willReturn($user);
////
////        $inventory = new Inventory();
////        $inventory->setUsername('testuser');
////
////        $inventoryRepo = $this->createMock(InventoryRepository::class);
////        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($inventory);
////
////
////        $controller = new InventoryController();
////        $response = $controller->getInventory($request, $inventoryRepo, $userRepo);
////
////        $this->assertEquals(401, $response->getStatusCode());
////        $this->assertInstanceOf(JsonResponse::class, $response);
//////        $data = json_decode($response->getContent(), true);
//////        $this->assertEquals('success', $data['status']);
//////        $this->assertEquals(0, $data['inventory']['trap']);
//////        $this->assertEquals(0, $data['inventory']['fake']);
//////        $this->assertEquals(0, $data['inventory']['poison']);
////    }
//
////    public function testGetInventoryUserNotLoggedIn(): void
////    {
////        $session = $this->createMock(SessionInterface::class);
////        $session->method('get')->with('strava_username')->willReturn(null);
////
////        $request = new Request([], [], [], [], [], [], json_encode([]));
////        $request ->setSession($session);
////
////        $em = $this->createMock(EntityManagerInterface::class);
////
////        $user = new User();
////        $user->setUsername('testuser');
////
////        $userRepo = $this->createMock(UserRepository::class);
////        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);
////
////        $inventory = new Inventory();
////        $inventory->setUsername('testuser');
////
////        $inventoryRepo = $this->createMock(InventoryRepository::class);
////        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($inventory);
////
////
////        $controller = new InventoryController();
////        $response = $controller->getInventory($request, $inventoryRepo, $userRepo);
////
////        $this->assertEquals(401, $response->getStatusCode());
////        $this->assertInstanceOf(JsonResponse::class, $response);
////        $data = json_decode($response->getContent(), true);
////        $this->assertEquals('error', $data['status']);
////        $this->assertEquals('User not logged in', $data['message']);
////    }
////
////    public function testGetInventoryUserNotFound(): void
////    {
////        $session = $this->createMock(SessionInterface::class);
////        $session->method('get')->with('strava_username')->willReturn('testuser');
////
////        $request = new Request([], [], [], [], [], [], json_encode([]));
////        $request ->setSession($session);
////
////        $em = $this->createMock(EntityManagerInterface::class);
////
////        $user = new User();
////        $user->setUsername('testuser');
////
////        $userRepo = $this->createMock(UserRepository::class);
////        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);
////
////        $inventory = new Inventory();
////        $inventory->setUsername('testuser');
////
////        $inventoryRepo = $this->createMock(InventoryRepository::class);
////        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($inventory);
////
////
////        $controller = new InventoryController();
////        $response = $controller->getInventory($request, $inventoryRepo, $userRepo);
////
////        $this->assertEquals(404, $response->getStatusCode());
////        $this->assertInstanceOf(JsonResponse::class, $response);
////        $data = json_decode($response->getContent(), true);
////        $this->assertEquals('error', $data['status']);
////        $this->assertEquals('User not found for inventory', $data['message']);
////    }
////
////    public function testGetInventoryNoInventoryYet(): void
////    {
////        $session = $this->createMock(SessionInterface::class);
////        $session->method('get')->with('userData')->willReturn('testuser');
////
////        $request = new Request([], [], [], [], [], [], json_encode([]));
////        $request ->setSession($session);
////
////        $em = $this->createMock(EntityManagerInterface::class);
////
////        $user = new User();
////        $user->setUsername('testuser');
////
////        $userRepo = $this->createMock(UserRepository::class);
////        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);
////
////        $inventory = new Inventory();
////        $inventory->setUsername('testuser');
////
////        $inventoryRepo = $this->createMock(InventoryRepository::class);
////        $inventoryRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);
////
////
////        $controller = new InventoryController();
////        $response = $controller->getInventory($request, $inventoryRepo, $userRepo);
////
////        $this->assertEquals(200, $response->getStatusCode());
////        $this->assertInstanceOf(JsonResponse::class, $response);
////        $data = json_decode($response->getContent(), true);
////        $this->assertEquals('success', $data['status']);
////        $this->assertEquals(0, $data['inventory']['trap']);
////        $this->assertEquals(0, $data['inventory']['fake']);
////        $this->assertEquals(0, $data['inventory']['poison']);
////    }
//}
