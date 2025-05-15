<?php
//
//namespace App\Tests\Controller\StravaApi;
//
//use App\Entity\User;
//use App\Repository\UserRepository;
//use Doctrine\ORM\EntityManagerInterface;
//
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\HttpClient\Exception\TransportException;
//use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
//use Symfony\Contracts\HttpClient\HttpClientInterface;
//use Symfony\Contracts\HttpClient\ResponseInterface;
//
//class CallbackWebTest extends WebTestCase
//{
//    public function testCallbackSuccessWithExistingUser()
//    {
//        $client = static::createClient();
//
//        $mockTokenResponse = $this->createMock(ResponseInterface::class);
//        $mockTokenResponse->method('toArray')->willReturn([
//            'access_token' => 'dummy_token',
//        ]);
//
//        $mockAthleteResponse = $this->createMock(ResponseInterface::class);
//        $mockAthleteResponse->method('toArray')->willReturn([
//            'id' => 1234,
//            'username' => 'stravatestuser',
//            'firstname' => 'Test',
//            'lastname' => 'User',
//        ]);
//
//        $mockHttpClient = $this->createMock(HttpClientInterface::class);
//        $mockHttpClient->method('request')->willReturnCallback(function ($method, $url) use ($mockTokenResponse, $mockAthleteResponse) {
//            if (str_contains($url, 'oauth/token')) {
//                return $mockTokenResponse;
//            }
//
//            if (str_contains($url, 'api/v3/athlete')) {
//                return $mockAthleteResponse;
//            }
//
//            throw new \Exception("Unexpected URL in test: " . $url);
//        });
//
//        $user = new User();
//        $user->setUsername('testuser');
//
//        $mockUserRepository = $this->createMock(UserRepository::class);
//        $mockUserRepository->method('findOneBy')->willReturn($user);
//
//        // Mock EntityManager to prevent actual DB operations
//        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
//
//        self::getContainer()->set(UserRepository::class, $mockUserRepository);
//        self::getContainer()->set(EntityManagerInterface::class, $mockEntityManager);
//        self::getContainer()->set(HttpClientInterface::class, $mockHttpClient);
//
//        $client->request('GET', '/strava/callback?code=xyz');
//
//        $this->assertResponseRedirects('/');
//    }
//
//    public function testCallbackSuccesWithoutExistingUser()
//    {
//        $client = static::createClient();
//
//        // Mock first Strava token response
//        $mockTokenResponse = $this->createMock(ResponseInterface::class);
//        $mockTokenResponse->method('toArray')->willReturn([
//            'access_token' => 'dummy_token',
//        ]);
//
//        // Mock second Strava athlete info response
//        $mockAthleteResponse = $this->createMock(ResponseInterface::class);
//        $mockAthleteResponse->method('toArray')->willReturn([
//            'id' => 1234,
//            'username' => 'stravatestuser',
//            'firstname' => 'Test',
//            'lastname' => 'User',
//        ]);
//
//        // Mock HttpClientInterface to return different responses based on URL
//        $mockHttpClient = $this->createMock(HttpClientInterface::class);
//        $mockHttpClient->method('request')->willReturnCallback(function ($method, $url) use ($mockTokenResponse, $mockAthleteResponse) {
//            if (str_contains($url, 'oauth/token')) {
//                return $mockTokenResponse;
//            }
//
//            if (str_contains($url, 'api/v3/athlete')) {
//                return $mockAthleteResponse;
//            }
//
//            throw new \Exception("Unexpected URL in test: " . $url);
//        });
//
//        $mockUserRepository = $this->createMock(UserRepository::class);
//        $mockUserRepository->method('findOneBy')->willReturn(null);
//
//        // Mock EntityManager to prevent actual DB operations
//        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
//        $mockEntityManager->expects($this->once())->method('persist');
//        $mockEntityManager->expects($this->once())->method('flush');
//
//        // Inject into container
//        self::getContainer()->set(UserRepository::class, $mockUserRepository);
//        self::getContainer()->set(EntityManagerInterface::class, $mockEntityManager);
//
//
//        // Inject mocked HttpClient into container
//        self::getContainer()->set(HttpClientInterface::class, $mockHttpClient);
//
//        // Perform request with code = xyz to trigger the specific if condition
//        $client->request('GET', '/strava/callback?code=xyz');
//
//        // Check that it redirected due to `if ($code == 'xyz')` branch
//        $this->assertResponseRedirects('/');
//    }
//
//    public function testCallbackUserHasNotGivenPermissionToStravaApi()
//    {
//        $client = static::createClient();
//        $client->request('GET', '/strava/callback?error=access_denied');
//        $this->assertResponseRedirects('/connect_strava');
//    }
//
//    public function testCallbackWithNoCode(){
//        $client = static::createClient();
//        $client->request('GET', '/strava/callback');
//        $this->assertResponseRedirects('/connect_strava');
//    }
//
//}
