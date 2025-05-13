<?php

namespace App\Tests\Controller\Stravabucks;

use App\Controller\StravabucksController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class StravabucksUseTest extends TestCase
{
    public function testUseStravabucksNotEnough()
    {
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(3);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['amount' => 10]));
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new StravabucksController();
        $response = $controller->useStravabucks($request, $em, $userRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Not enough stravabucks', $data['message']);
    }

    public function testUseStravabucksEnough()
    {
        $user = new User();
        $user->setUsername('testuser')->setStravabucks(50);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('strava_username')->willReturn('testuser');

        $request = new Request([], [], [], [], [], [], json_encode(['amount' => 10]));
        $request->setSession($session);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['username' => 'testuser'])->willReturn($user);

        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new StravabucksController();
        $response = $controller->useStravabucks($request, $em, $userRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Purchase successful', $data['message']);
        $this->assertEquals('40', $data['current_balance']);
    }
}
