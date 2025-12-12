<?php

namespace App\Repository;

use App\Entity\Covoiturage;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Covoiturage>
 */
class CovoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Covoiturage::class);
    }

    /**
     * Recherche de covoiturages avec filtres avancés
     */
    public function findByRechercheAvecFiltres(
        string $villeDepart,
        string $villeArrivee,
        \DateTimeInterface $dateDepart,
        ?\DateTimeInterface $dateArrivee = null,
        bool $filtrerParHeure = false,
        ?bool $ecologique = null,
        ?int $prixMax = null,
        ?int $dureeMax = null,
        ?int $noteMin = null
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.ville_depart) LIKE LOWER(:villeDepart)')
            ->andWhere('LOWER(c.ville_arrivee) LIKE LOWER(:villeArrivee)')
            ->andWhere('c.places_restantes > 0')
            ->setParameter('villeDepart', '%' . $villeDepart . '%')
            ->setParameter('villeArrivee', '%' . $villeArrivee . '%');

        // Filtrer par date de départ
        if ($filtrerParHeure) {
            // Si une heure est spécifiée, chercher à partir de cette heure
            $qb->andWhere('c.date_depart >= :dateDepart')
               ->setParameter('dateDepart', $dateDepart);
        } else {
            // Sinon, chercher tous les covoiturages de la journée
            $dateDebut = new \DateTime($dateDepart->format('Y-m-d') . ' 00:00:00');
            $dateFin = new \DateTime($dateDepart->format('Y-m-d') . ' 23:59:59');
            $qb->andWhere('c.date_depart BETWEEN :dateDebut AND :dateFin')
               ->setParameter('dateDebut', $dateDebut)
               ->setParameter('dateFin', $dateFin);
        }

        // Filtrer par date d'arrivée si spécifiée
        if ($dateArrivee) {
            $qb->andWhere('c.date_arrivee <= :dateArrivee')
               ->setParameter('dateArrivee', $dateArrivee);
        }

        // Filtre écologique
        if ($ecologique === true) {
            $qb->andWhere('c.ecologique = true');
        }

        // Filtre prix maximum
        if ($prixMax !== null) {
            $qb->andWhere('c.prix <= :prixMax')
               ->setParameter('prixMax', $prixMax);
        }

        return $qb->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver le prochain covoiturage disponible après une date donnée
     */
    public function findProchainCovoiturage(
        string $villeDepart,
        string $villeArrivee,
        \DateTimeInterface $apresDate
    ): ?Covoiturage {
        // Chercher à partir de la fin de la journée
        $finJournee = new \DateTime($apresDate->format('Y-m-d') . ' 23:59:59');
        
        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.ville_depart) LIKE LOWER(:villeDepart)')
            ->andWhere('LOWER(c.ville_arrivee) LIKE LOWER(:villeArrivee)')
            ->andWhere('c.date_depart > :apresDate')
            ->andWhere('c.places_restantes > 0')
            ->setParameter('villeDepart', '%' . $villeDepart . '%')
            ->setParameter('villeArrivee', '%' . $villeArrivee . '%')
            ->setParameter('apresDate', $finJournee)
            ->orderBy('c.date_depart', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche simple par ville et date (méthode de secours)
     */
    public function findByVillesEtDate(
        string $villeDepart,
        string $villeArrivee,
        \DateTimeInterface $date
    ): array {
        $dateDebut = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $dateFin = new \DateTime($date->format('Y-m-d') . ' 23:59:59');

        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.ville_depart) LIKE LOWER(:villeDepart)')
            ->andWhere('LOWER(c.ville_arrivee) LIKE LOWER(:villeArrivee)')
            ->andWhere('c.date_depart BETWEEN :dateDebut AND :dateFin')
            ->andWhere('c.places_restantes > 0')
            ->setParameter('villeDepart', '%' . $villeDepart . '%')
            ->setParameter('villeArrivee', '%' . $villeArrivee . '%')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Covoiturages passés d'un chauffeur
     */
    public function findCovoituragesPasses(Utilisateur $chauffeur): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur_id = :chauffeur')
            ->andWhere('c.date_depart < :now')
            ->setParameter('chauffeur', $chauffeur)
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Covoiturages à venir d'un chauffeur
     */
    public function findCovoituragesAVenir(Utilisateur $chauffeur): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur_id = :chauffeur')
            ->andWhere('c.date_depart >= :now')
            ->setParameter('chauffeur', $chauffeur)
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Tous les covoiturages disponibles (places > 0, date future)
     */
    public function findAllDisponibles(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.places_restantes > 0')
            ->andWhere('c.date_depart >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre total de covoiturages
     */
    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de covoiturages par jour (pour statistiques)
     */
    public function getCovoituragesParJour(int $nbJours = 30): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE(date_depart) as jour,
                COUNT(id) as total
            FROM covoiturage
            WHERE date_depart >= DATE_SUB(NOW(), INTERVAL :nbJours DAY)
            GROUP BY DATE(date_depart)
            ORDER BY jour ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['nbJours' => $nbJours]);

        return $result->fetchAllAssociative();
    }

    /**
     * Compte les covoiturages créés ce mois-ci
     */
    public function countCovoituragesCeMois(): int
    {
        $debut = new \DateTime('first day of this month 00:00:00');
        $fin = new \DateTime('last day of this month 23:59:59');

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.date_depart >= :debut')
            ->andWhere('c.date_depart <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Statistiques par mois (pour graphiques admin)
     */
    public function getStatistiquesParMois(int $nbMois = 6): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $dateDebut = new \DateTime("-{$nbMois} months");
        $dateDebutStr = $dateDebut->format('Y-m-d');

        $sql = "
            SELECT 
                DATE_FORMAT(date_depart, '%Y-%m') as mois,
                COUNT(id) as total_covoiturages,
                SUM(CASE WHEN ecologique = 1 THEN 1 ELSE 0 END) as covoiturages_eco
            FROM covoiturage
            WHERE date_depart >= '{$dateDebutStr}'
            GROUP BY DATE_FORMAT(date_depart, '%Y-%m')
            ORDER BY mois ASC
        ";

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();
    }

    /**
     * Top chauffeurs (par nombre de covoiturages)
     */
    public function getTopChauffeurs(int $limit = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                u.id,
                u.pseudo,
                u.photo,
                COUNT(c.id) as nb_covoiturages,
                SUM(CASE WHEN c.ecologique = 1 THEN 1 ELSE 0 END) as nb_eco
            FROM covoiturage c
            JOIN utilisateur u ON c.utilisateur_id = u.id
            GROUP BY u.id, u.pseudo, u.photo
            ORDER BY nb_covoiturages DESC
            LIMIT {$limit}
        ";

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();
    }

    /**
     * Trajets les plus populaires
     */
    public function getTrajetsPopulaires(int $limit = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                ville_depart,
                ville_arrivee,
                COUNT(id) as nb_trajets
            FROM covoiturage
            GROUP BY ville_depart, ville_arrivee
            ORDER BY nb_trajets DESC
            LIMIT {$limit}
        ";

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();
    }

    /**
     * Covoiturages du jour
     */
    public function findCovoituragesDuJour(): array
    {
        $debut = new \DateTime('today 00:00:00');
        $fin = new \DateTime('today 23:59:59');

        return $this->createQueryBuilder('c')
            ->andWhere('c.date_depart >= :debut')
            ->andWhere('c.date_depart <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques globales
     */
    public function getStatistiques(): array
    {
        // Total covoiturages
        $totalCovoiturages = $this->countTotal();

        // Covoiturages écologiques
        $covoituragesEco = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.ecologique = true')
            ->getQuery()
            ->getSingleScalarResult();

        // Pourcentage écologique
        $pourcentageEco = $totalCovoiturages > 0 
            ? round(($covoituragesEco / $totalCovoiturages) * 100, 1) 
            : 0;

        return [
            'total' => $totalCovoiturages,
            'ecologiques' => $covoituragesEco,
            'pourcentage_eco' => $pourcentageEco,
        ];
    }
}