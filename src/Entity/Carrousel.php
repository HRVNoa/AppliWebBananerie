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

    #[ORM\Column(length: 50, nullable:true)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'carrousel', targetEntity: Media::class)]
    private Collection $media;

    #[ORM\OneToOne(mappedBy: 'carrousel', cascade: ['persist', 'remove'])]
    private ?Espace $espace = null;

    #[ORM\OneToOne(mappedBy: 'portfolio', cascade: ['persist', 'remove'])]
    private ?Independant $independant = null;


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

    public function addMedia(Media $media): static
    {
        if (!$this->media->contains($media)) {
            $this->media->add($media);
            $media->setCarrousel($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->media->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getCarrousel() === $this) {
                $media->setCarrousel(null);
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

    public function getIndependant(): ?Independant
    {
        return $this->independant;
    }

    public function setIndependant(?Independant $independant): static
    {
        // unset the owning side of the relation if necessary
        if ($independant === null && $this->independant !== null) {
            $this->independant->setPortfolio(null);
        }

        // set the owning side of the relation if necessary
        if ($independant !== null && $independant->getPortfolio() !== $this) {
            $independant->setPortfolio($this);
        }

        $this->independant = $independant;

        return $this;
    }

}
