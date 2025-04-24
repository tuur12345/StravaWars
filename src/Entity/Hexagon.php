<?php

namespace App\Entity;

use App\Repository\HexagonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HexagonRepository::class)]
#[ORM\Table(name: 'hexagon')]
class Hexagon
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $latitude = null;

    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }
}
