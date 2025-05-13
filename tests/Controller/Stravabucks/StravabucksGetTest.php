<?php

namespace App\Tests\Controller\Stravabucks;

use App\Controller\StravabucksController;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StravabucksGetTest extends TestCase
{
    public function testGetStravabucksSuccess()
    {
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(42);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request();
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $controller = new StravabucksController();
        $response = $controller->getStravabucks($request, $userRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals(42, $data['stravabucks']);
    }

    public function testGetStravabucksUserNotLoggedIn()
    {
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(42);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn(null);

        $request = new Request();
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $controller = new StravabucksController();
        $response = $controller->getStravabucks($request, $userRepo);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('User not logged in', $data['message']);
    }

    public function testGetStravabucksUserNotFound()
    {
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(42);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request();
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn(null);

        $controller = new StravabucksController();
        $response = $controller->getStravabucks($request, $userRepo);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('User not found', $data['message']);
    }
}
