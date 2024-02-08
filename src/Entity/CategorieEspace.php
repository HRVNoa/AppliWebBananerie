<?php

namespace App\Entity;

use App\Repository\CategorieEspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieEspaceRepository::class)]
class CategorieEspace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: TypeEspace::class)]
    private Collection $typeEspaces;

    public function __construct()
    {
        $this->typeEspaces = new ArrayCollection();
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
     * @return Collection<int, TypeEspace>
     */
    public function getTypeEspaces(): Collection
    {
        return $this->typeEspaces;
    }

    public function addTypeEspace(TypeEspace $typeEspace): static
    {
        if (!$this->typeEspaces->contains($typeEspace)) {
            $this->typeEspaces->add($typeEspace);
            $typeEspace->setCategorie($this);
        }

        return $this;
    }

    public function removeTypeEspace(TypeEspace $typeEspace): static
    {
        if ($this->typeEspaces->removeElement($typeEspace)) {
            // set the owning side to null (unless already changed)
            if ($typeEspace->getCategorie() === $this) {
                $typeEspace->setCategorie(null);
            }
        }

        return $this;
    }
}
