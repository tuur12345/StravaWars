<?php

namespace App\Tests\Controller\StravaApi;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CallbackWebTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();

        // Mock first Strava token response
        $mockTokenResponse = $this->createMock(ResponseInterface::class);
        $mockTokenResponse->method('toArray')->willReturn([
            'access_token' => 'dummy_token',
        ]);

        // Mock second Strava athlete info response
        $mockAthleteResponse = $this->createMock(ResponseInterface::class);
        $mockAthleteResponse->method('toArray')->willReturn([
            'id' => 1234,
            'username' => 'stravatestuser',
            'firstname' => 'Test',
            'lastname' => 'User',
        ]);

        // Mock HttpClientInterface to return different responses based on URL
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')->willReturnCallback(function ($method, $url) use ($mockTokenResponse, $mockAthleteResponse) {
            if (str_contains($url, 'oauth/token')) {
                return $mockTokenResponse;
            }

            if (str_contains($url, 'api/v3/athlete')) {
                return $mockAthleteResponse;
            }

            throw new \Exception("Unexpected URL in test: " . $url);
        });

        // Mock UserRepository to simulate "user not found"
        $mockUserRepository = $this->createMock(UserRepository::class);
        $mockUserRepository->method('findOneBy')->willReturn(null);

        // Mock EntityManager to prevent actual DB operations
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->expects($this->once())->method('persist');
        $mockEntityManager->expects($this->once())->method('flush');

        // Inject into container
        self::getContainer()->set(UserRepository::class, $mockUserRepository);
        self::getContainer()->set(EntityManagerInterface::class, $mockEntityManager);


        // Inject mocked HttpClient into container
        self::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        // Perform request with code = xyz to trigger the specific if condition
        $client->request('GET', '/strava/callback?code=xyz');

        // Check that it redirected due to `if ($code == 'xyz')` branch
        $this->assertResponseRedirects('/');
    }
}
