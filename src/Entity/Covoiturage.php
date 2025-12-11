<?php

// src/Entity/Covoiturage.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'covoiturage')]
class Covoiturage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'covoiturages')]
    #[ORM\JoinColumn(name: "utilisateur_id", referencedColumnName: "id", nullable: false)]
    private ?Utilisateur $utilisateur_id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $ville_depart = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $ville_arrivee = null;
    
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_depart = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $prix = null;

    #[ORM\Column(type: 'integer')]
    private ?int $places_restantes = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $ecologique = null;

    #[ORM\ManyToOne(targetEntity: Vehicule::class)]
    #[ORM\JoinColumn(name: "vehicule_id", referencedColumnName: "id", nullable: false)]
    private ?Vehicule $vehicule_id = null;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'covoiturage_id')]
    private Collection $participations;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'covoiturage_id')]
    private Collection $avis;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    // ==============================
    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateurId(): ?Utilisateur
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateurId(?Utilisateur $utilisateur_id): static
    {
        $this->utilisateur_id = $utilisateur_id;
        return $this;
    }

    public function getVilleDepart(): ?string
    {
        return $this->ville_depart;
    }

    public function setVilleDepart(string $ville_depart): static
    {
        $this->ville_depart = $ville_depart;
        return $this;
    }

    public function getVilleArrivee(): ?string
    {
        return $this->ville_arrivee;
    }

    public function setVilleArrivee(string $ville_arrivee): static
    {
        $this->ville_arrivee = $ville_arrivee;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }

    public function setDateDepart(\DateTimeInterface $date_depart): static
    {
        $this->date_depart = $date_depart;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getPlacesRestantes(): ?int
    {
        return $this->places_restantes;
    }

    public function setPlacesRestantes(int $places_restantes): static
    {
        $this->places_restantes = $places_restantes;
        return $this;
    }

    public function isEcologique(): ?bool
    {
        return $this->ecologique;
    }

    public function setEcologique(bool $ecologique): static
    {
        $this->ecologique = $ecologique;
        return $this;
    }

    public function getVehiculeId(): ?Vehicule
    {
        return $this->vehicule_id;
    }

    public function setVehiculeId(?Vehicule $vehicule_id): static
    {
        $this->vehicule_id = $vehicule_id;
        return $this;
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setCovoiturageId($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getCovoiturageId() === $this) {
                $participation->setCovoiturageId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setCovoiturageId($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getCovoiturageId() === $this) {
                $avi->setCovoiturageId(null);
            }
        }

        return $this;
    }
}
