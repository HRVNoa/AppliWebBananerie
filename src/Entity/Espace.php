<?php

namespace App\Entity;

use App\Repository\EspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EspaceRepository::class)]
class Espace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'], inversedBy: 'espaces')]
    private ?Carrousel $carrousel = null;

    #[ORM\ManyToOne(inversedBy: 'espaces')]
    private ?TypeEspace $typeEspace = null;

    #[ORM\OneToMany(mappedBy: 'espace', targetEntity: EquipementEspace::class)]
    private Collection $equipementEspaces;

    #[ORM\OneToMany(mappedBy: 'espace', targetEntity: Reservation::class, cascade: ['persist', 'remove'])]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'espace', targetEntity: TarifEspaceTarif::class , cascade: ['persist', 'remove'] )]
    private Collection $tarifEspaceTarifs;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'espace', targetEntity: Remboursement::class)]
    private Collection $remboursements;

    #[ORM\OneToMany(mappedBy: 'espace', targetEntity: ReservationLog::class)]
    private Collection $reservationLogs;

    public function __construct()
    {
        $this->equipementEspaces = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->tarifEspaceTarifs = new ArrayCollection();
        $this->remboursements = new ArrayCollection();
        $this->reservationLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCarrousel(): ?Carrousel
    {
        return $this->carrousel;
    }

    public function setCarrousel(?Carrousel $carrousel): static
    {
        $this->carrousel = $carrousel;

        return $this;
    }

    public function getTypeEspace(): ?TypeEspace
    {
        return $this->typeEspace;
    }

    public function setTypeEspace(?TypeEspace $typeEspace): static
    {
        $this->typeEspace = $typeEspace;

        return $this;
    }

    /**
     * @return Collection<int, EquipementEspace>
     */
    public function getEquipementEspaces(): Collection
    {
        return $this->equipementEspaces;
    }

    public function addEquipementEspace(EquipementEspace $equipementEspace): static
    {
        if (!$this->equipementEspaces->contains($equipementEspace)) {
            $this->equipementEspaces->add($equipementEspace);
            $equipementEspace->setEspace($this);
        }

        return $this;
    }

    public function removeEquipementEspace(EquipementEspace $equipementEspace): static
    {
        if ($this->equipementEspaces->removeElement($equipementEspace)) {
            // set the owning side to null (unless already changed)
            if ($equipementEspace->getEspace() === $this) {
                $equipementEspace->setEspace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setEspace($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getEspace() === $this) {
                $reservation->setEspace(null);
            }
        }

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
            $tarifEspaceTarif->setEspace($this);
        }

        return $this;
    }

    public function removeTarifEspaceTarif(TarifEspaceTarif $tarifEspaceTarif): static
    {
        if ($this->tarifEspaceTarifs->removeElement($tarifEspaceTarif)) {
            // set the owning side to null (unless already changed)
            if ($tarifEspaceTarif->getEspace() === $this) {
                $tarifEspaceTarif->setEspace(null);
            }
        }

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

    /**
     * @return Collection<int, Remboursement>
     */
    public function getRemboursements(): Collection
    {
        return $this->remboursements;
    }

    public function addRemboursement(Remboursement $remboursement): static
    {
        if (!$this->remboursements->contains($remboursement)) {
            $this->remboursements->add($remboursement);
            $remboursement->setEspace($this);
        }

        return $this;
    }

    public function removeRemboursement(Remboursement $remboursement): static
    {
        if ($this->remboursements->removeElement($remboursement)) {
            // set the owning side to null (unless already changed)
            if ($remboursement->getEspace() === $this) {
                $remboursement->setEspace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReservationLog>
     */
    public function getReservationLogs(): Collection
    {
        return $this->reservationLogs;
    }

    public function addReservationLog(ReservationLog $reservationLog): static
    {
        if (!$this->reservationLogs->contains($reservationLog)) {
            $this->reservationLogs->add($reservationLog);
            $reservationLog->setEspace($this);
        }

        return $this;
    }

    public function removeReservationLog(ReservationLog $reservationLog): static
    {
        if ($this->reservationLogs->removeElement($reservationLog)) {
            // set the owning side to null (unless already changed)
            if ($reservationLog->getEspace() === $this) {
                $reservationLog->setEspace(null);
            }
        }

        return $this;
    }
}
