<?php

namespace App\Repository;

use App\Entity\Entreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entreprise>
 */
class EntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreprise::class);
    }

    /**
     * Pagine et recherche les entreprises avec eager loading du user
     * (factorise le QB duplique de Daip\EntrepriseController - audit P3).
     *
     * @return array{entreprises: Entreprise[], total: int, totalPages: int, page: int}
     */
    public function searchPaginated(string $search, int $page, int $perPage): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')->addSelect('u')
            ->orderBy('e.nom', 'ASC');

        if ($search !== '') {
            $qb->andWhere('LOWER(e.nom) LIKE :q OR LOWER(u.email) LIKE :q')
                ->setParameter('q', '%' . strtolower($search) . '%');
        }

        $total = (int) (clone $qb)->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $totalPages);

        $entreprises = $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return [
            'entreprises' => $entreprises,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
        ];
    }
}
