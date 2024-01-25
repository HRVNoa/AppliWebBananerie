<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'tag', targetEntity: IndependantTag::class)]
    private Collection $independantTags;

    public function __construct()
    {
        $this->independantTags = new ArrayCollection();
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
     * @return Collection<int, IndependantTag>
     */
    public function getIndependantTags(): Collection
    {
        return $this->independantTags;
    }

    public function addIndependantTag(IndependantTag $independantTag): static
    {
        if (!$this->independantTags->contains($independantTag)) {
            $this->independantTags->add($independantTag);
            $independantTag->setTag($this);
        }

        return $this;
    }

    public function removeIndependantTag(IndependantTag $independantTag): static
    {
        if ($this->independantTags->removeElement($independantTag)) {
            // set the owning side to null (unless already changed)
            if ($independantTag->getTag() === $this) {
                $independantTag->setTag(null);
            }
        }

        return $this;
    }
}
