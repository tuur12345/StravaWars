<?php

namespace App\Tests;

use App\Entity\Hexagon;
use PHPUnit\Framework\TestCase;

class HexagonTest extends TestCase
{
    public function testHexagonGettersAndSetters(): void
    {
        $hexagon = new Hexagon();

        $latitude = '51.5074N';
        $longitude = '0.1278W';
        $color = '#FF5733';
        $owner = 'test_user';
        $level = 3;

        $hexagon->setLatitude($latitude);
        $hexagon->setLongitude($longitude);
        $hexagon->setColor($color);
        $hexagon->setOwner($owner);
        $hexagon->setLevel($level);

        $this->assertSame($latitude, $hexagon->getLatitude());
        $this->assertSame($longitude, $hexagon->getLongitude());
        $this->assertSame($color, $hexagon->getColor());
        $this->assertSame($owner, $hexagon->getOwner());
        $this->assertSame($level, $hexagon->getLevel());
    }
}

