<?php

namespace App\Entity;

use App\Repository\CarrouselRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarrouselRepository::class)]
class Carrousel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'carrousel', targetEntity: Media::class)]
    private Collection $media;

    #[ORM\OneToMany(mappedBy: 'carrousel', targetEntity: Espace::class)]
    private Collection $espace;

    public function __construct()
    {
        $this->media = new ArrayCollection();
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
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): static
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setCarrousel($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getCarrousel() === $this) {
                $medium->setCarrousel(null);
            }
        }

        return $this;
    }

    public function getEspace(): ?Espace
    {
        return $this->espace;
    }

    public function setEspace(?Espace $espace): static
    {
        // unset the owning side of the relation if necessary
        if ($espace === null && $this->espace !== null) {
            $this->espace->setCarrousel(null);
        }

        // set the owning side of the relation if necessary
        if ($espace !== null && $espace->getCarrousel() !== $this) {
            $espace->setCarrousel($this);
        }

        $this->espace = $espace;

        return $this;
    }
}
