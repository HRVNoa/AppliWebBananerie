<?php

namespace App\Entity;

use App\Repository\MetierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MetierRepository::class)]
class Metier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'metier', targetEntity: Independant::class)]
    private Collection $independants;

    #[ORM\OneToMany(mappedBy: 'metierSecondaire', targetEntity: Independant::class)]
    private Collection $independants2nd;

    #[ORM\OneToMany(mappedBy: 'metier', targetEntity: Paiement::class)]
    private Collection $paiements;

    public function __construct()
    {
        $this->independants = new ArrayCollection();
        $this->independants2nd = new ArrayCollection();
        $this->paiements = new ArrayCollection();
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
            $independant->setMetier($this);
        }

        return $this;
    }

    public function removeIndependant(Independant $independant): static
    {
        if ($this->independants->removeElement($independant)) {
            // set the owning side to null (unless already changed)
            if ($independant->getMetier() === $this) {
                $independant->setMetier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Independant>
     */
    public function getIndependants2nd(): Collection
    {
        return $this->independants2nd;
    }

    public function addIndependants2nd(Independant $independants2nd): static
    {
        if (!$this->independants2nd->contains($independants2nd)) {
            $this->independants2nd->add($independants2nd);
            $independants2nd->setMetierSecondaire($this);
        }

        return $this;
    }

    public function removeIndependants2nd(Independant $independants2nd): static
    {
        if ($this->independants2nd->removeElement($independants2nd)) {
            // set the owning side to null (unless already changed)
            if ($independants2nd->getMetierSecondaire() === $this) {
                $independants2nd->setMetierSecondaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setMetier($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getMetier() === $this) {
                $paiement->setMetier(null);
            }
        }

        return $this;
    }
}
