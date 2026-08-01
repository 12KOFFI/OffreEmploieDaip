<?php

namespace App\Repository;

use App\Entity\Offre;
use App\Enum\StatutOffre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offre>
 */
class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

    /**
     * @param array{q?: string, ville?: string, secteur?: int, typeContrat?: string} $criteres
     * @return Offre[]
     */
    public function rechercherOffresPubliees(array $criteres = [], ?int $limit = null, int $offset = 0): array
    {
        $qb = $this->baseQueryBuilderOffresPubliees($criteres)
            ->orderBy('o.datePublication', 'DESC')
            ->setFirstResult($offset);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array{q?: string, ville?: string, secteur?: int, typeContrat?: string} $criteres
     */
    public function compterOffresPubliees(array $criteres = []): int
    {
        return (int) $this->baseQueryBuilderOffresPubliees($criteres)
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array{q?: string, ville?: string, secteur?: int, typeContrat?: string} $criteres
     */
    private function baseQueryBuilderOffresPubliees(array $criteres): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.statut = :statut')
            ->setParameter('statut', StatutOffre::PUBLIEE)
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.secteur', 's')->addSelect('s');

        if (!empty($criteres['q'])) {
            $qb->andWhere('o.titre LIKE :q OR e.nom LIKE :q')
                ->setParameter('q', '%' . $criteres['q'] . '%');
        }

        if (!empty($criteres['ville'])) {
            $qb->andWhere('o.ville LIKE :ville')
                ->setParameter('ville', '%' . $criteres['ville'] . '%');
        }

        if (!empty($criteres['secteur'])) {
            $qb->andWhere('s.id = :secteur')
                ->setParameter('secteur', $criteres['secteur']);
        }

        if (!empty($criteres['typeContrat'])) {
            $qb->andWhere('o.typeContrat = :typeContrat')
                ->setParameter('typeContrat', $criteres['typeContrat']);
        }

        return $qb;
    }

    /**
     * @return Offre[]
     */
    public function findByFilters(array $criteres = []): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.secteur', 's')->addSelect('s');

        if (!empty($criteres['statut'])) {
            $qb->andWhere('o.statut = :statut')
                ->setParameter('statut', $criteres['statut']);
        }

        if (!empty($criteres['secteur'])) {
            $qb->andWhere('s.id = :secteur')
                ->setParameter('secteur', $criteres['secteur']);
        }

        if (!empty($criteres['ville'])) {
            $qb->andWhere('o.ville LIKE :ville')
                ->setParameter('ville', '%' . $criteres['ville'] . '%');
        }

        if (!empty($criteres['dateDebut'])) {
            $qb->andWhere('o.datePublication >= :dateDebut')
                ->setParameter('dateDebut', $criteres['dateDebut']);
        }

        if (!empty($criteres['dateFin'])) {
            $qb->andWhere('o.datePublication <= :dateFin')
                ->setParameter('dateFin', $criteres['dateFin']);
        }

        return $qb->orderBy('o.datePublication', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les offres ventilees par statut (pour le dashboard DAIP).
     *
     * @return array<string, int> Ex: ['brouillon' => 5, 'publiee' => 12, ...]
     */
    public function countByStatut(): array
    {
        $rows = $this->createQueryBuilder('o')
            ->select('o.statut AS statut, COUNT(o.id) AS total')
            ->groupBy('o.statut')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['statut']->value] = (int) $row['total'];
        }

        return $result;
    }

    /**
     * Compte les offres ventilees par statut pour une entreprise donnee.
     *
     * @return array<string, int>
     */
    public function countByStatutForEntreprise(\App\Entity\Entreprise $entreprise): array
    {
        $rows = $this->createQueryBuilder('o')
            ->select('o.statut AS statut, COUNT(o.id) AS total')
            ->andWhere('o.entreprise = :entreprise')
            ->setParameter('entreprise', $entreprise)
            ->groupBy('o.statut')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['statut']->value] = (int) $row['total'];
        }

        return $result;
    }
}
