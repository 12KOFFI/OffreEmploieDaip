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
        // Etape 1 : recuperer les IDs pagines sans JOIN de collection (evite le probleme
        // Doctrine "LIMIT/OFFSET with fetch joins" qui charge tout en memoire).
        $idsQb = $this->baseQueryBuilderOffresPubliees($criteres)
            ->select('DISTINCT o.id, o.datePublication')
            ->orderBy('o.datePublication', 'DESC')
            ->setFirstResult($offset);

        if ($limit !== null) {
            $idsQb->setMaxResults($limit);
        }

        $ids = array_column($idsQb->getQuery()->getScalarResult(), 'id');

        if (empty($ids)) {
            return [];
        }

        // Etape 2 : charger les entites completes avec JOIN uniquement sur les IDs selectionnes.
        return $this->createQueryBuilder('o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
            ->leftJoin('om.metier', 'm')->addSelect('m')
            ->orderBy('o.datePublication', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array{q?: string, ville?: string, secteur?: int, typeContrat?: string} $criteres
     */
    public function compterOffresPubliees(array $criteres = []): int
    {
        return (int) $this->baseQueryBuilderOffresPubliees($criteres)
            ->select('COUNT(DISTINCT o.id)')
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
            ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
            ->leftJoin('om.metier', 'm')->addSelect('m');

        if (!empty($criteres['q'])) {
            $qb->andWhere('o.titre LIKE :q OR e.nom LIKE :q OR m.nom LIKE :q')
                ->setParameter('q', '%' . $criteres['q'] . '%');
        }

        if (!empty($criteres['ville'])) {
            $qb->andWhere('om.ville LIKE :ville')
                ->setParameter('ville', '%' . $criteres['ville'] . '%');
        }

        if (!empty($criteres['metier'])) {
            $qb->andWhere('m.id = :metier')
                ->setParameter('metier', $criteres['metier']);
        }

        if (!empty($criteres['typeContrat'])) {
            $qb->andWhere('om.typeContrat = :typeContrat')
                ->setParameter('typeContrat', $criteres['typeContrat']);
        }

        return $qb;
    }

    /**
     * @return Offre[]
     */
    public function findByFilters(array $criteres = []): array
    {
        return $this->baseQueryBuilderFilters($criteres)
            ->orderBy('o.datePublication', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Pagine les resultats en BDD avec pagination par IDs (evite LIMIT/OFFSET avec fetch joins).
     *
     * @return Offre[]
     */
    public function findByFiltersPaginated(array $criteres = [], int $limit = 9, int $offset = 0): array
    {
        // Etape 1 : IDs pagines sans JOIN de collection
        $ids = array_column(
            $this->baseQueryBuilderFilters($criteres)
                ->select('DISTINCT o.id, o.datePublication')
                ->orderBy('o.datePublication', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getScalarResult(),
            'id'
        );

        if (empty($ids)) {
            return [];
        }

        // Etape 2 : entites completes uniquement sur ces IDs
        return $this->createQueryBuilder('o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
            ->leftJoin('om.metier', 'm')->addSelect('m')
            ->orderBy('o.datePublication', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les offres correspondant aux filtres DAIP, sans doublons dus aux JOINs.
     */
    public function countByFilters(array $criteres = []): int
    {
        return (int) $this->baseQueryBuilderFilters($criteres)
            ->select('COUNT(DISTINCT o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * QueryBuilder de base pour les filtres DAIP (findByFilters, countByFilters).
     */
    private function baseQueryBuilderFilters(array $criteres): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
            ->leftJoin('om.metier', 'm')->addSelect('m');

        if (!empty($criteres['statut'])) {
            $statutEnum = \App\Enum\StatutOffre::tryFrom($criteres['statut']);
            if ($statutEnum !== null) {
                $qb->andWhere('o.statut = :statut')
                    ->setParameter('statut', $statutEnum);
            }
        }

        if (!empty($criteres['metier'])) {
            $qb->andWhere('m.id = :metier')
                ->setParameter('metier', $criteres['metier']);
        }

        if (!empty($criteres['ville'])) {
            $qb->andWhere('om.ville LIKE :ville')
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

        return $qb;
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
