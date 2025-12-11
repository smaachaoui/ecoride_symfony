<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(name: "utilisateur_id", referencedColumnName: "id", nullable: false)]
    private ?Utilisateur $utilisateur_id = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(name: "covoiturage_id", referencedColumnName: "id", nullable: false)]
    private ?Covoiturage $covoiturage_id = null;

    #[ORM\Column]
    private ?bool $confirme = null;

    #[ORM\Column]
    private ?int $credits_utilises = null;

    #[ORM\Column(type: 'string', columnDefinition: "ENUM('en_attente', 'accepte', 'refuse') DEFAULT 'en_attente'")]
    private ?string $statut = 'en_attente';

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

    public function getCovoiturageId(): ?Covoiturage
    {
        return $this->covoiturage_id;
    }

    public function setCovoiturageId(?Covoiturage $covoiturage_id): static
    {
        $this->covoiturage_id = $covoiturage_id;

        return $this;
    }

    public function isConfirme(): ?bool
    {
        return $this->confirme;
    }

    public function setConfirme(bool $confirme): static
    {
        $this->confirme = $confirme;

        return $this;
    }

    public function getCreditsUtilises(): ?int
    {
        return $this->credits_utilises;
    }

    public function setCreditsUtilises(int $credits_utilises): static
    {
        $this->credits_utilises = $credits_utilises;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function isEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    public function isAccepte(): bool
    {
        return $this->statut === 'accepte';
    }

    public function isRefuse(): bool
    {
        return $this->statut === 'refuse';
    }

}
