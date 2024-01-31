<?php

namespace App\Entity;

use App\Repository\EquipementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'equipement', targetEntity: EquipementEspace::class)]
    private Collection $equipementEspaces;

    public function __construct()
    {
        $this->equipementEspaces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, EquipementEspace>
     */
    public function getEquipementEspaces(): Collection
    {
        return $this->equipementEspaces;
    }

    public function addEquipementEspace(EquipementEspace $equipementEspace): static
    {
        if (!$this->equipementEspaces->contains($equipementEspace)) {
            $this->equipementEspaces->add($equipementEspace);
            $equipementEspace->setEquipement($this);
        }

        return $this;
    }

    public function removeEquipementEspace(EquipementEspace $equipementEspace): static
    {
        if ($this->equipementEspaces->removeElement($equipementEspace)) {
            // set the owning side to null (unless already changed)
            if ($equipementEspace->getEquipement() === $this) {
                $equipementEspace->setEquipement(null);
            }
        }

        return $this;
    }
}
