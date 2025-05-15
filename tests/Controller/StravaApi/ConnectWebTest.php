<?php

namespace App\Tests\Controller\StravaApi;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnectWebTest extends WebTestCase
{
    public function testStravaRedirect(){
        $client = static::createClient();
        $client->request('GET', '/strava/connect');

        $this->assertResponseRedirects();
        $this->assertStringStartsWith('https://www.strava.com/oauth/authorize', $client->getResponse()->headers->get('Location'));
    }
}
