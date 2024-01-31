<?php

namespace App\Entity;

use App\Repository\TarifEspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifEspaceRepository::class)]
class TarifEspace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\OneToMany(mappedBy: 'tarifEspace', targetEntity: TarifEspaceTarif::class)]
    private Collection $tarifEspaceTarifs;

    public function __construct()
    {
        $this->tarifEspaceTarifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection<int, TarifEspaceTarif>
     */
    public function getTarifEspaceTarifs(): Collection
    {
        return $this->tarifEspaceTarifs;
    }

    public function addTarifEspaceTarif(TarifEspaceTarif $tarifEspaceTarif): static
    {
        if (!$this->tarifEspaceTarifs->contains($tarifEspaceTarif)) {
            $this->tarifEspaceTarifs->add($tarifEspaceTarif);
            $tarifEspaceTarif->setTarifEspace($this);
        }

        return $this;
    }

    public function removeTarifEspaceTarif(TarifEspaceTarif $tarifEspaceTarif): static
    {
        if ($this->tarifEspaceTarifs->removeElement($tarifEspaceTarif)) {
            // set the owning side to null (unless already changed)
            if ($tarifEspaceTarif->getTarifEspace() === $this) {
                $tarifEspaceTarif->setTarifEspace(null);
            }
        }

        return $this;
    }
}
