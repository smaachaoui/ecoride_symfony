<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByPseudo(string $pseudo): ?Utilisateur
    {
        return $this->findOneBy(['pseudo' => $pseudo]);
    }

    /**
     * Compte les utilisateurs par rôle(s)
     */
    public function countByRole(array $roles): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.role IN (:roles)')
            ->setParameter('roles', $roles)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Recherche avec filtres
     */
    public function findByFilters(string $search = '', string $role = '', string $statut = ''): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.created_at', 'DESC');

        if (!empty($search)) {
            $qb->andWhere('u.pseudo LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($role)) {
            if ($role === 'CHAUFFEUR') {
                $qb->andWhere('u.role IN (:roles)')
                   ->setParameter('roles', ['CHAUFFEUR', 'CHAUFFEUR_PASSAGER']);
            } elseif ($role === 'PASSAGER') {
                $qb->andWhere('u.role IN (:roles)')
                   ->setParameter('roles', ['PASSAGER', 'CHAUFFEUR_PASSAGER']);
            } else {
                $qb->andWhere('u.role = :role')
                   ->setParameter('role', $role);
            }
        }

        if ($statut === 'actif') {
            $qb->andWhere('u.is_suspended = false OR u.is_suspended IS NULL');
        } elseif ($statut === 'suspendu') {
            $qb->andWhere('u.is_suspended = true');
        }

        return $qb->getQuery()->getResult();
    }
}