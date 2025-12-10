<?php

// src/Repository/CovoiturageRepository.php

namespace App\Repository;

use App\Entity\Covoiturage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CovoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Covoiturage::class);
    }

    // Méthode pour rechercher des covoiturages par ville et date
    public function findBySearch($villeDepart, $villeArrivee, $dateDepart)
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if ($villeDepart) {
            $queryBuilder->andWhere('c.ville_depart LIKE :villeDepart')
                         ->setParameter('villeDepart', '%'.$villeDepart.'%');
        }

        if ($villeArrivee) {
            $queryBuilder->andWhere('c.ville_arrivee LIKE :villeArrivee')
                         ->setParameter('villeArrivee', '%'.$villeArrivee.'%');
        }

        if ($dateDepart) {
            $queryBuilder->andWhere('c.date_depart = :dateDepart')
                         ->setParameter('dateDepart', $dateDepart);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
