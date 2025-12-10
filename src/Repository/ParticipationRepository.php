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

    public function findParticipationsPassees(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->join('p.covoiturage_id', 'c')
            ->addSelect('c')
            ->andWhere('c.date_depart <= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.date_depart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total des crédits gagnés par la plateforme (2 crédits par participation)
     */
    public function calculerCreditsPlateforme(): int
    {
        $result = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) * 2')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * Crédits plateforme par mois
     */
    public function getCreditsParMois(int $nbMois = 6): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(c.date_depart, '%Y-%m') as mois,
                COUNT(p.id) * 2 as credits
            FROM participation p
            JOIN covoiturage c ON p.covoiturage_id_id = c.id
            WHERE c.date_depart >= DATE_SUB(NOW(), INTERVAL :nbMois MONTH)
            GROUP BY DATE_FORMAT(c.date_depart, '%Y-%m')
            ORDER BY mois ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['nbMois' => $nbMois]);

        return $result->fetchAllAssociative();
    }
}