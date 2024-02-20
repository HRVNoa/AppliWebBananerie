<?php

namespace App\Entity;

use App\Repository\IndependantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AcmeAssert;

#[ORM\Entity(repositoryClass: IndependantRepository::class)]
class Independant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $entreprise = null;

    #[ORM\Column(length: 13)]
    #[Assert\Regex(pattern : "/^\d+$/", message:"Veuillez saisir uniquement des chiffres.")]

    #[Assert\Length( max: 13, maxMessage:"La telephone ne peut pas dépasser 13 chiffres.")]
    private ?string $tel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[AcmeAssert\IsOldEnough]
    #[AcmeAssert\IsAgeNotTooHigh]
    private ?\DateTimeInterface $dateNaiss = null;

    #[ORM\Column(length: 5)]
    #[Assert\Range(
        notInRangeMessage: "le code postal doit être entre 10000 et 99999",
        min : 10000,
        max : 99999
    )]
    private ?string $copos = null;

    #[ORM\ManyToOne(inversedBy: 'independants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Statut $statut = null;

    #[ORM\ManyToOne(inversedBy: 'independants')]
    private ?Metier $metier = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    private ?string $ville = null;

    #[ORM\OneToOne(inversedBy: 'independant', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'independant', targetEntity: IndependantTag::class, cascade: ['persist', 'remove'])]
    private Collection $independantTags;

    #[ORM\Column]
    private ?bool $annuaire = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $photodeprofil = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $facebook = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $linkedin = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $youtube = null;


    #[ORM\ManyToOne(inversedBy: 'independants2nd')]
    private ?Metier $metierSecondaire = null;


    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length( max: 500, maxMessage:"La description ne peut pas dépasser 500 caractères.")]
    private ?string $description = null;

    #[ORM\OneToOne(inversedBy: 'independant', cascade: ['persist', 'remove'])]
    private ?Carrousel $portfolio = null;

    #[ORM\OneToMany(mappedBy: 'independant', targetEntity: Video::class , cascade: ['persist', 'remove'])]
    private Collection $videos;


    public function __construct()
    {
        $this->independantTags = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }

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

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(?string $entreprise): static
    {
        $this->entreprise = $entreprise;

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

    public function getDateNaiss(): ?\DateTimeInterface
    {
        return $this->dateNaiss;
    }

    public function setDateNaiss(\DateTimeInterface $dateNaiss): static
    {
        $this->dateNaiss = $dateNaiss;

        return $this;
    }

    public function getCopos(): ?string
    {
        return $this->copos;
    }

    public function setCopos(string $copos): static
    {
        $this->copos = $copos;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(?Statut $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getMetier(): ?Metier
    {
        return $this->metier;
    }

    public function setMetier(?Metier $metier): static
    {
        $this->metier = $metier;

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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

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
            $independantTag->setIndependant($this);
        }

        return $this;
    }

    public function removeIndependantTag(IndependantTag $independantTag): static
    {
        if ($this->independantTags->removeElement($independantTag)) {
            // set the owning side to null (unless already changed)
            if ($independantTag->getIndependant() === $this) {
                $independantTag->setIndependant(null);
            }
        }

        return $this;
    }

    public function isAnnuaire(): ?bool
    {
        return $this->annuaire;
    }

    public function setAnnuaire(bool $annuaire): static
    {
        $this->annuaire = $annuaire;

        return $this;
    }

    public function getPhotodeprofil(): ?string
    {
        return $this->photodeprofil;
    }

    public function setPhotodeprofil(?string $photodeprofil): static
    {
        $this->photodeprofil = $photodeprofil;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): static
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): static
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): static
    {
        $this->youtube = $youtube;

        return $this;
    }


    public function getMetierSecondaire(): ?Metier
    {
        return $this->metierSecondaire;
    }

    public function setMetierSecondaire(?Metier $metierSecondaire): static
    {
        $this->metierSecondaire = $metierSecondaire;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPortfolio(): ?Carrousel
    {
        return $this->portfolio;
    }

    public function setPortfolio(?Carrousel $portfolio): static
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setIndependant($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getIndependant() === $this) {
                $video->setIndependant(null);
            }
        }

        return $this;
    }

}
