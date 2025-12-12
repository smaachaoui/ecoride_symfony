<?php

namespace App\Repository;

use App\Entity\Participation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation>
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    /**
     * Participations à venir (toutes, peu importe le statut)
     */
    public function findParticipationsAVenir(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->join('p.covoiturage_id', 'c')
            ->addSelect('c')
            ->andWhere('c.date_depart > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Participations passées (seulement les acceptées)
     */
    public function findParticipationsPassees(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', 'accepte')
            ->join('p.covoiturage_id', 'c')
            ->addSelect('c')
            ->andWhere('c.date_depart <= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Participations acceptées à venir (pour le passager)
     */
    public function findParticipationsAcceptees(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', 'accepte')
            ->join('p.covoiturage_id', 'c')
            ->addSelect('c')
            ->andWhere('c.date_depart > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Participations en attente (demandes envoyées par l'utilisateur)
     */
    public function findParticipationsEnAttente(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->join('p.covoiturage_id', 'c')
            ->addSelect('c')
            ->andWhere('c.date_depart > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Demandes de participation en attente pour les covoiturages d'un chauffeur
     */
    public function findDemandesEnAttentePourChauffeur(Utilisateur $chauffeur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->join('p.covoiturage_id', 'c')
            ->addSelect('c')
            ->andWhere('c.utilisateur_id = :chauffeur')
            ->setParameter('chauffeur', $chauffeur)
            ->andWhere('c.date_depart > :now')
            ->setParameter('now', new \DateTime())
            ->join('p.utilisateur_id', 'u')
            ->addSelect('u')
            ->orderBy('c.date_depart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les demandes en attente pour un chauffeur
     */
    public function countDemandesEnAttentePourChauffeur(Utilisateur $chauffeur): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->join('p.covoiturage_id', 'c')
            ->andWhere('c.utilisateur_id = :chauffeur')
            ->setParameter('chauffeur', $chauffeur)
            ->andWhere('c.date_depart > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule le total des crédits gagnés par la plateforme (2 crédits par participation acceptée)
     */
    public function calculerCreditsPlateforme(): int
    {
        $result = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) * 2')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', 'accepte')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * Crédits plateforme par mois (seulement participations acceptées)
     */
    public function getCreditsParMois(int $nbMois = 6): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $dateDebut = new \DateTime("-{$nbMois} months");
        $dateDebutStr = $dateDebut->format('Y-m-d');

        $sql = "
            SELECT 
                DATE_FORMAT(c.date_depart, '%Y-%m') as mois,
                COUNT(p.id) * 2 as credits
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id = c.id
            WHERE c.date_depart >= '{$dateDebutStr}'
            AND p.statut = 'accepte'
            GROUP BY DATE_FORMAT(c.date_depart, '%Y-%m')
            ORDER BY mois ASC
        ";

        $result = $conn->executeQuery($sql);

        return $result->fetchAllAssociative();
    }
}