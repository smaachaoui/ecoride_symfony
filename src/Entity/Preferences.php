<?php

namespace App\Entity;

use App\Repository\PreferencesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreferencesRepository::class)]
class Preferences
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id', nullable: false)]
    private ?Utilisateur $utilisateur_id = null;

    #[ORM\Column]
    private ?bool $accepte_fumeurs = null;

    #[ORM\Column]
    private ?bool $accepte_animaux = null;

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

    public function isAccepteFumeurs(): ?bool
    {
        return $this->accepte_fumeurs;
    }

    public function setAccepteFumeurs(bool $accepte_fumeurs): static
    {
        $this->accepte_fumeurs = $accepte_fumeurs;

        return $this;
    }

    public function isAccepteAnimaux(): ?bool
    {
        return $this->accepte_animaux;
    }

    public function setAccepteAnimaux(bool $accepte_animaux): static
    {
        $this->accepte_animaux = $accepte_animaux;

        return $this;
    }
}
