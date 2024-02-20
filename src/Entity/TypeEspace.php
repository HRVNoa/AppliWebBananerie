<?php

namespace App\Entity;

use App\Repository\TypeEspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeEspaceRepository::class)]
class TypeEspace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'typeEspace', targetEntity: Espace::class)]
    private Collection $espaces;

    #[ORM\ManyToOne(inversedBy: 'typeEspaces')]
    private ?CategorieEspace $categorie = null;

    public function __construct()
    {
        $this->espaces = new ArrayCollection();
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
     * @return Collection<int, Espace>
     */
    public function getEspaces(): Collection
    {
        return $this->espaces;
    }

    public function addEspace(Espace $espace): static
    {
        if (!$this->espaces->contains($espace)) {
            $this->espaces->add($espace);
            $espace->setTypeEspace($this);
        }

        return $this;
    }

    public function removeEspace(Espace $espace): static
    {
        if ($this->espaces->removeElement($espace)) {
            // set the owning side to null (unless already changed)
            if ($espace->getTypeEspace() === $this) {
                $espace->setTypeEspace(null);
            }
        }

        return $this;
    }

    public function getCategorie(): ?CategorieEspace
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieEspace $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
}
