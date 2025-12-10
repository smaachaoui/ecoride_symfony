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
     * Recherche les covoiturages avec filtres avancés
     */
    public function findByRechercheAvecFiltres(
        string $villeDepart,
        string $villeArrivee,
        \DateTime $date,
        ?bool $ecologique = null,
        ?int $prixMax = null,
        ?int $dureeMax = null,
        ?int $noteMin = null
    ): array {
        $dateDebut = (clone $date)->setTime(0, 0, 0);
        $dateFin = (clone $date)->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.utilisateur_id', 'u')
            ->leftJoin('c.vehicule_id', 'v')
            ->addSelect('u', 'v')
            ->andWhere('LOWER(c.ville_depart) LIKE LOWER(:villeDepart)')
            ->andWhere('LOWER(c.ville_arrivee) LIKE LOWER(:villeArrivee)')
            ->andWhere('c.date_depart BETWEEN :dateDebut AND :dateFin')
            ->andWhere('c.places_restantes > 0')
            ->setParameter('villeDepart', '%' . $villeDepart . '%')
            ->setParameter('villeArrivee', '%' . $villeArrivee . '%')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);

        // Filtre écologique
        if ($ecologique === true) {
            $qb->andWhere('c.ecologique = :ecologique')
               ->setParameter('ecologique', true);
        }

        // Filtre prix maximum
        if ($prixMax !== null) {
            $qb->andWhere('c.prix <= :prixMax')
               ->setParameter('prixMax', $prixMax);
        }

        // Filtre durée maximum (en minutes)
        if ($dureeMax !== null) {
            $qb->andWhere('c.duree <= :dureeMax')
               ->setParameter('dureeMax', $dureeMax);
        }

        $qb->orderBy('c.date_depart', 'ASC');

        $results = $qb->getQuery()->getResult();

        // Filtre note minimale (post-query car nécessite calcul)
        if ($noteMin !== null) {
            $results = array_filter($results, function (Covoiturage $covoiturage) use ($noteMin) {
                $noteMoyenne = $this->getNoteMoyenneChauffeur($covoiturage->getUtilisateurId()->getId());
                return $noteMoyenne !== null && $noteMoyenne >= $noteMin;
            });
        }

        return array_values($results);
    }

    /**
     * Recherche les covoiturages par ville de départ, arrivée et date
     * Ne retourne que les trajets avec au moins une place disponible
     */
    public function findByRecherche(string $villeDepart, string $villeArrivee, \DateTime $date): array
    {
        return $this->findByRechercheAvecFiltres($villeDepart, $villeArrivee, $date);
    }

    /**
     * Trouve le prochain covoiturage disponible après une date donnée
     */
    public function findProchainCovoiturage(string $villeDepart, string $villeArrivee, \DateTime $date): ?Covoiturage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.ville_depart) LIKE LOWER(:villeDepart)')
            ->andWhere('LOWER(c.ville_arrivee) LIKE LOWER(:villeArrivee)')
            ->andWhere('c.date_depart > :date')
            ->andWhere('c.places_restantes > 0')
            ->setParameter('villeDepart', '%' . $villeDepart . '%')
            ->setParameter('villeArrivee', '%' . $villeArrivee . '%')
            ->setParameter('date', $date)
            ->orderBy('c.date_depart', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Calcule la note moyenne d'un chauffeur
     */
    public function getNoteMoyenneChauffeur(int $utilisateurId): ?float
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('AVG(a.note) as moyenne')
            ->from('App\Entity\Avis', 'a')
            ->join('a.covoiturage_id', 'c')
            ->where('c.utilisateur_id = :utilisateurId')
            ->setParameter('utilisateurId', $utilisateurId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float) $result, 1) : null;
    }

    public function findCovoituragesPasses(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur_id = :utilisateur')
            ->andWhere('c.date_depart < :now')
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les covoiturages d'une journée
    */
    public function findCovoituragesDuJour(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date_depart >= :dateDebut')
            ->andWhere('c.date_depart < :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
    * Compte les covoiturages du mois en cours
     */
    public function countCovoituragesCeMois(): int
    {
        $debut = new \DateTime('first day of this month midnight');
        $fin = new \DateTime('last day of this month 23:59:59');

        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.date_depart >= :debut')
            ->andWhere('c.date_depart <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Statistiques par mois
     */
    public function getStatistiquesParMois(int $nbMois = 6): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(date_depart, '%Y-%m') as mois,
                COUNT(*) as nb_covoiturages,
                SUM(GREATEST(0, (SELECT COUNT(*) FROM participation p WHERE p.covoiturage_id_id = c.id))) as nb_participations
            FROM covoiturage c
            WHERE date_depart >= DATE_SUB(NOW(), INTERVAL :nbMois MONTH)
            GROUP BY DATE_FORMAT(date_depart, '%Y-%m')
            ORDER BY mois ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['nbMois' => $nbMois]);

        return $result->fetchAllAssociative();
    }

    /**
     * Top chauffeurs
     */
    public function getTopChauffeurs(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->select('u.pseudo, u.photo, COUNT(c.id) as nb_trajets')
            ->join('c.utilisateur_id', 'u')
            ->groupBy('u.id')
            ->orderBy('nb_trajets', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trajets les plus populaires
     */
    public function getTrajetsPopulaires(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.ville_depart, c.ville_arrivee, COUNT(c.id) as nb_trajets')
            ->groupBy('c.ville_depart, c.ville_arrivee')
            ->orderBy('nb_trajets', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}