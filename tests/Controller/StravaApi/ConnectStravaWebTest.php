<?php

namespace App\Tests\Controller\StravaApi;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnectStravaWebTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/connect_strava');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('header img[alt="stravawars icon"]');
        $this->assertSelectorExists('body img[alt="strava connect button"]');
    }
}
