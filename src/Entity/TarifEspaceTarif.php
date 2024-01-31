<?php

namespace App\Entity;

use App\Repository\TarifEspaceTarifRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifEspaceTarifRepository::class)]
class TarifEspaceTarif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    #[ORM\ManyToOne(inversedBy: 'tarifEspaceTarifs')]
    private ?Espace $espace = null;

    #[ORM\ManyToOne(inversedBy: 'tarifEspaceTarifs')]
    private ?TarifEspace $tarifEspace = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getEspace(): ?Espace
    {
        return $this->espace;
    }

    public function setEspace(?Espace $espace): static
    {
        $this->espace = $espace;

        return $this;
    }

    public function getTarifEspace(): ?TarifEspace
    {
        return $this->tarifEspace;
    }

    public function setTarifEspace(?TarifEspace $tarifEspace): static
    {
        $this->tarifEspace = $tarifEspace;

        return $this;
    }
}
