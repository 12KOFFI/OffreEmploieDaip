<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Utilise automatiquement par Symfony Security lorsque l'algo de hachage change
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Liste les comptes DAIP (DAIP + DAIP_ADMIN). Les valeurs recherchees sont
     * entourees de guillemets pour matcher l'encodage JSON exact du role et
     * eviter les faux positifs sur un role qui contiendrait "ROLE_DAIP" comme
     * simple sous-chaine (audit A6).
     *
     * @return User[]
     */
    public function findAllDaip(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :roleDaip OR u.roles LIKE :roleDaipAdmin')
            ->setParameter('roleDaip', '%"ROLE_DAIP"%')
            ->setParameter('roleDaipAdmin', '%"ROLE_DAIP_ADMIN"%')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
