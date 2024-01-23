<?php

namespace App\Entity;

use App\Repository\StatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatutRepository::class)]
class Statut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'statut', targetEntity: Independant::class)]
    private Collection $independants;

    public function __construct()
    {
        $this->independants = new ArrayCollection();
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
     * @return Collection<int, Independant>
     */
    public function getIndependants(): Collection
    {
        return $this->independants;
    }

    public function addIndependant(Independant $independant): static
    {
        if (!$this->independants->contains($independant)) {
            $this->independants->add($independant);
            $independant->setStatut($this);
        }

        return $this;
    }

    public function removeIndependant(Independant $independant): static
    {
        if ($this->independants->removeElement($independant)) {
            // set the owning side to null (unless already changed)
            if ($independant->getStatut() === $this) {
                $independant->setStatut(null);
            }
        }

        return $this;
    }
}
