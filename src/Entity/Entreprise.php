<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150)]
    private ?string $nomStructure = null;

    #[ORM\Column(length: 255)]
    private ?string $adresseStructure = null;

    #[ORM\Column(length: 150)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $fonctionStructure = null;

    #[ORM\ManyToOne(inversedBy: 'entreprises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SecteurActivite $secteuractivite = null;

    #[ORM\OneToOne(inversedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(length: 13)]
    #[Assert\Regex(pattern : "/^\d+$/", message:"Veuillez saisir uniquement des chiffres.")]
    #[Assert\Length( max: 13, maxMessage:"La telephone ne peut pas dépasser 13 chiffres.")]
    private ?string $tel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNomStructure(): ?string
    {
        return $this->nomStructure;
    }

    public function setNomStructure(string $nomStructure): static
    {
        $this->nomStructure = $nomStructure;

        return $this;
    }

    public function getAdresseStructure(): ?string
    {
        return $this->adresseStructure;
    }

    public function setAdresseStructure(string $adresseStructure): static
    {
        $this->adresseStructure = $adresseStructure;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFonctionStructure(): ?string
    {
        return $this->fonctionStructure;
    }

    public function setFonctionStructure(string $fonctionStructure): static
    {
        $this->fonctionStructure = $fonctionStructure;

        return $this;
    }

    public function getSecteuractivite(): ?SecteurActivite
    {
        return $this->secteuractivite;
    }

    public function setSecteuractivite(?SecteurActivite $secteuractivite): static
    {
        $this->secteuractivite = $secteuractivite;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }
}
