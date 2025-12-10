<?php

namespace App\Repository;

use App\Entity\Covoiturage;
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
}