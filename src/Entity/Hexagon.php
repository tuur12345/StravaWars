<?php

namespace App\Entity;

use App\Repository\HexagonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HexagonRepository::class)]
class Hexagon
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?String $latitude = null;

    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private ?String $longitude = null;

    #[ORM\Column(length: 7)]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $owner = null;

    #[ORM\Column]
    private ?int $level = null;

    public function getLatitude(): ?String
    {
        return $this->latitude;
    }

    public function setLatitude(String $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?String
    {
        return $this->longitude;
    }

    public function setLongitude(String $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(?string $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }
}
