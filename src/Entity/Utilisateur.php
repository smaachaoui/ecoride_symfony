<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet email')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà utilisé')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_PASSAGER = 'PASSAGER';
    public const ROLE_CHAUFFEUR = 'CHAUFFEUR';
    public const ROLE_CHAUFFEUR_PASSAGER = 'CHAUFFEUR_PASSAGER';
    public const ROLE_EMPLOYE = 'EMPLOYE';
    public const ROLE_ADMIN = 'ADMIN';

    public const CREDITS_INSCRIPTION = 20;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?int $credits = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $role = null;

    #[ORM\Column(type: 'json')]
    private array $roles_system = [];

    #[ORM\Column(nullable: true)]
    private ?bool $is_profile_configured = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_suspended = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Covoiturage>
     */
    #[ORM\OneToMany(targetEntity: Covoiturage::class, mappedBy: 'utilisateur_id')]
    private Collection $covoiturages;

    /**
     * @var Collection<int, Vehicule>
     */
    #[ORM\OneToMany(targetEntity: Vehicule::class, mappedBy: 'utilisateur_id')]
    private Collection $vehicules;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'utilisateur_id')]
    private Collection $participations;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'utilisateur_id')]
    private Collection $avis;

    #[ORM\OneToOne(mappedBy: 'utilisateur_id', cascade: ['persist', 'remove'])]
    private ?Preferences $preferences = null;

    public function __construct()
    {
        $this->covoiturages = new ArrayCollection();
        $this->vehicules = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->credits = self::CREDITS_INSCRIPTION;
        $this->role = self::ROLE_PASSAGER;
        $this->roles_system = ['ROLE_USER'];
        $this->is_profile_configured = false;
        $this->is_suspended = false;
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(?int $credits): static
    {
        $this->credits = $credits;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function isProfileConfigured(): ?bool
    {
        return $this->is_profile_configured;
    }

    public function setIsProfileConfigured(?bool $is_profile_configured): static
    {
        $this->is_profile_configured = $is_profile_configured;
        return $this;
    }

    public function isSuspended(): ?bool
    {
        return $this->is_suspended;
    }

    public function setIsSuspended(?bool $is_suspended): static
    {
        $this->is_suspended = $is_suspended;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    // ============================================
    // Implémentation UserInterface
    // ============================================

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles_system;
        $roles[] = 'ROLE_USER';

        // Ajouter les rôles spécifiques
        if ($this->role === self::ROLE_EMPLOYE) {
            $roles[] = 'ROLE_EMPLOYE';
        }
        if ($this->role === self::ROLE_ADMIN) {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }

    public function setRolesSystem(array $roles_system): static
    {
        $this->roles_system = $roles_system;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données sensibles temporaires, effacez-les ici
    }

    // ============================================
    // Méthodes utilitaires
    // ============================================

    public function isChauffeur(): bool
    {
        return in_array($this->role, [self::ROLE_CHAUFFEUR, self::ROLE_CHAUFFEUR_PASSAGER]);
    }

    public function isPassager(): bool
    {
        return in_array($this->role, [self::ROLE_PASSAGER, self::ROLE_CHAUFFEUR_PASSAGER]);
    }

    public function isEmploye(): bool
    {
        return $this->role === self::ROLE_EMPLOYE;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // ============================================
    // Relations
    // ============================================

    /**
     * @return Collection<int, Covoiturage>
     */
    public function getCovoiturages(): Collection
    {
        return $this->covoiturages;
    }

    public function addCovoiturage(Covoiturage $covoiturage): static
    {
        if (!$this->covoiturages->contains($covoiturage)) {
            $this->covoiturages->add($covoiturage);
            $covoiturage->setUtilisateurId($this);
        }
        return $this;
    }

    public function removeCovoiturage(Covoiturage $covoiturage): static
    {
        if ($this->covoiturages->removeElement($covoiturage)) {
            if ($covoiturage->getUtilisateurId() === $this) {
                $covoiturage->setUtilisateurId(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Vehicule>
     */
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    public function addVehicule(Vehicule $vehicule): static
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules->add($vehicule);
            $vehicule->setUtilisateurId($this);
        }
        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): static
    {
        if ($this->vehicules->removeElement($vehicule)) {
            if ($vehicule->getUtilisateurId() === $this) {
                $vehicule->setUtilisateurId(null);
            }
        }
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
            $participation->setUtilisateurId($this);
        }
        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            if ($participation->getUtilisateurId() === $this) {
                $participation->setUtilisateurId(null);
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
            $avi->setUtilisateurId($this);
        }
        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getUtilisateurId() === $this) {
                $avi->setUtilisateurId(null);
            }
        }
        return $this;
    }

    public function getPreferences(): ?Preferences
    {
        return $this->preferences;
    }

    public function setPreferences(?Preferences $preferences): static
    {
        if ($preferences === null && $this->preferences !== null) {
            $this->preferences->setUtilisateurId(null);
        }

        if ($preferences !== null && $preferences->getUtilisateurId() !== $this) {
            $preferences->setUtilisateurId($this);
        }

        $this->preferences = $preferences;
        return $this;
    }
}