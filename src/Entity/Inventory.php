<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column]
    private ?int $trap = 0;

    #[ORM\Column]
    private ?int $fake = 0;

    #[ORM\Column]
    private ?int $poison = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getTrap(): ?int
    {
        return $this->trap;
    }

    public function setTrap(int $trap): static
    {
        $this->trap = $trap;

        return $this;
    }

    public function getFake(): ?int
    {
        return $this->fake;
    }

    public function setFake(int $fake): static
    {
        $this->fake = $fake;

        return $this;
    }

    public function getPoison(): ?int
    {
        return $this->poison;
    }

    public function setPoison(int $poison): static
    {
        $this->poison = $poison;

        return $this;
    }

    public function addItem(string $itemType, int $quantity): static
    {
        switch ($itemType) {
            case 'trap':
                $this->trap += $quantity;
                break;
            case 'fake':
                $this->fake += $quantity;
                break;
            case 'poison':
                $this->poison += $quantity;
                break;
        }

        return $this;
    }
}