<?php

namespace App\Factory;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\AmpHttpClient;

class AmpHttpClientFactory
{
    public static function create(): HttpClientInterface
    {
        return new AmpHttpClient();
    }
}
