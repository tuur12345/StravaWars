<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class InventoryTest extends TestCase
{
    public function testId(){
        $inventory = new \App\Entity\Inventory();
        $this->assertNull($inventory->getId());
    }

    public function testUsername(){
        $inventory = new \App\Entity\Inventory();
        $inventory->setUsername('testuser');
        $this->assertSame('testuser', $inventory->getUsername());
    }

    public function testTrap(){
        $user = new \App\Entity\Inventory();
        $user->setTrap(1);
        $this->assertSame(1, $user->getTrap());
    }

    public function testFake(){
        $inventory = new \App\Entity\Inventory();
        $inventory->setFake(1);
        $this->assertSame(1, $inventory->getFake());
    }

    public function testPoison(){
        $inventory = new \App\Entity\Inventory();
        $inventory->setPoison(1);
        $this->assertSame(1, $inventory->getPoison());
    }

    public function testAddItem(){
        $inventory = new \App\Entity\Inventory();
        $inventory->addItem('trap', 1);
        $this->assertSame(1, $inventory->getTrap());

        $inventory = new \App\Entity\Inventory();
        $inventory->addItem('fake', 2);
        $this->assertSame(2, $inventory->getFake());

        $inventory = new \App\Entity\Inventory();
        $inventory->addItem('poison', 0);
        $this->assertSame(0, $inventory->getPoison());
    }
}
