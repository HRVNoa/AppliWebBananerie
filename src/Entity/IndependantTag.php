<?php

namespace App\Entity;

use App\Repository\IndependantTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IndependantTagRepository::class)]
class IndependantTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $super = null;

    #[ORM\ManyToOne(inversedBy: 'independantTags')]
    private ?Independant $independant = null;

    #[ORM\ManyToOne(inversedBy: 'independantTags')]
    private ?Tag $tag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isSuper(): ?bool
    {
        return $this->super;
    }

    public function setSuper(?bool $super): static
    {
        $this->super = $super;

        return $this;
    }

    public function getIndependant(): ?Independant
    {
        return $this->independant;
    }

    public function setIndependant(?Independant $independant): static
    {
        $this->independant = $independant;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
