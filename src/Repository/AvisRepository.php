<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * Compte les avis validés aujourd'hui
     */
    public function countAvisValidesAujourdHui(): int
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.statut = :statut')
            ->andWhere('a.created_at >= :today')
            ->andWhere('a.created_at < :tomorrow')
            ->setParameter('statut', Avis::STATUT_VALIDE)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les avis traités récemment
     */
    public function findAvisTraitesRecents(int $limit = 20): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.statut != :statut')
            ->setParameter('statut', Avis::STATUT_EN_ATTENTE)
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les avis validés d'un covoiturage
     */
    public function findAvisValidesByCovoiturage(int $covoiturageId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.covoiturage_id = :covoiturageId')
            ->andWhere('a.statut = :statut')
            ->setParameter('covoiturageId', $covoiturageId)
            ->setParameter('statut', Avis::STATUT_VALIDE)
            ->orderBy('a.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}