<?php

// tests/Controller/StravaControllerTest.php

namespace App\Tests\Controller\Stravabucks;

use App\Controller\StravabucksController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StravabucksAddTest extends TestCase
{
    public function testBucksAddedWithSuccess(){
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(10);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturnMap([
            ['kudos_converted_this_session', false, false],  // First call
            ['strava_username', null, 'testuser'],           // Second call
        ]);


        $request = new Request([], [], [], [], [], [], json_encode(['amount' => 5]));
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($user);
        $em->expects($this->once())->method('flush');

        $controller = new StravabucksController();
        $response = $controller->addStravabucks($request, $em, $userRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Stravabucks added successfully', $data['message']);
        $this->assertEquals(15, $data['current_balance']);
    }

    public function testBucksNotConvertedAndNotLoggedIn(){
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(10);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturnMap([
            ['kudos_converted_this_session', false, false],  // First call
            ['strava_username', null, null],           // Second call
        ]);

        $request = new Request([], [], [], [], [], [], json_encode(['amount' => 5]));
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);

        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new StravabucksController();
        $response = $controller->addStravabucks($request, $em, $userRepo);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('User not logged in', $data['message']);
    }

    public function testBucksNotConvertedAndUserNotFound(){
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(10);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturnMap([
            ['kudos_converted_this_session', false, false],  // First call
            ['strava_username', null, 'testuser'],           // Second call
        ]);

        $request = new Request([], [], [], [], [], [], json_encode(['amount' => 5]));
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new StravabucksController();
        $response = $controller->addStravabucks($request, $em, $userRepo);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('User not found', $data['message']);
    }


    public function testBucksAlreadyConvertedWithExistingUser(){
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(10);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturnMap([
            ['kudos_converted_this_session', false, true],
            ['strava_username', null, 'testuser'],
        ]);

        $request = new Request([], [], [], [], [], [], json_encode(['amount' => 5]));
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $em = $this->createMock(EntityManagerInterface::class);
        $controller = new StravabucksController();
        $response = $controller->addStravabucks($request, $em, $userRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Kudos already converted in this session.', $data['message']);
        $this->assertEquals(10, $data['current_balance']);
    }

    public function testBucksAlreadyConvertedWithNoExistingUser(){
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturnMap([
            ['kudos_converted_this_session', false, true],
            ['strava_username', null, null],
        ]);

        $request = new Request( [], [], [], [], [], [], json_encode(['amount' => 5]));
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $controller = new StravabucksController();
        $response = $controller->addStravabucks($request, $em, $userRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Kudos already converted in this session.', $data['message']);
        $this->assertEquals(0, $data['current_balance']);
    }
}