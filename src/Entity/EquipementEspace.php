<?php

namespace App\Entity;

use App\Repository\EquipementEspaceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementEspaceRepository::class)]
class EquipementEspace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantit�e = null;

    #[ORM\ManyToOne(inversedBy: 'equipementEspaces')]
    private ?Espace $espace = null;

    #[ORM\ManyToOne(inversedBy: 'equipementEspaces')]
    private ?Equipement $equipement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantit�e(): ?int
    {
        return $this->quantit�e;
    }

    public function setQuantit�e(int $quantit�e): static
    {
        $this->quantit�e = $quantit�e;

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

    public function getEquipement(): ?Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): static
    {
        $this->equipement = $equipement;

        return $this;
    }
}
