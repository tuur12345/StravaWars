<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUsername(): void
    {
        $user = new \App\Entity\User();
        $user->setUsername('testuser');
        $this->assertSame('testuser', $user->getUsername());
    }
    public function testStravabucks()
    {
        $user = new \App\Entity\User();
        $user->setStravabucks(100);
        $this->assertSame(100, $user->getStravabucks());
    }

    public function testColor()
    {
        $user = new \App\Entity\User();
        $user->setColor('red');
        $this->assertSame('red', $user->getColor());

        $user = new \App\Entity\User();
        $user->setColor('#A21A05');
        $this->assertSame('#A21A05', $user->getColor());
    }

    public function testChainedSetters()
    {
        $user = new \App\Entity\User();
        $this->assertSame($user, $user->setUsername('alice')->setStravabucks(42));
    }

    public function testInitialIdIsNull()
    {
        $user = new \App\Entity\User();
        $this->assertNull($user->getId());
    }
}
