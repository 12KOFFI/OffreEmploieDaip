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
     * QueryBuilder de base pour les offres publiees (recherche publique).
     * N'ajoute les JOIN que si les criteres les necessitent, et ne fait
     * jamais d'addSelect ici : le select est decide par l'appelant
     * (COUNT pour le comptage, o.id seul pour la pagination par IDs).
     *
     * @param array{q?: string, ville?: string, secteur?: int, typeContrat?: string} $criteres
     */
    private function baseQueryBuilderOffresPubliees(array $criteres): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.statut = :statut')
            ->setParameter('statut', StatutOffre::PUBLIEE);

        $besoinEntreprise = !empty($criteres['q']);
        $besoinOffreMetier = !empty($criteres['q']) || !empty($criteres['ville']) || !empty($criteres['metier']) || !empty($criteres['typeContrat']);
        $besoinMetier = !empty($criteres['q']) || !empty($criteres['metier']);

        if ($besoinEntreprise) {
            $qb->leftJoin('o.entreprise', 'e');
        }
        if ($besoinOffreMetier) {
            $qb->leftJoin('o.offreMetiers', 'om');
        }
        if ($besoinMetier) {
            $qb->leftJoin('om.metier', 'm');
        }

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

    /**
     * Dernieres offres avec eager loading (entreprise + offreMetiers + metier),
     * pour les dashboards Entreprise et DAIP (audit P1).
     *
     * @return Offre[]
     */
    public function findLatestWithRelations(?\App\Entity\Entreprise $entreprise = null, int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
            ->leftJoin('om.metier', 'm')->addSelect('m')
            ->orderBy('o.datePublication', 'DESC')
            ->setMaxResults($limit);

        if ($entreprise !== null) {
            $qb->andWhere('o.entreprise = :entreprise')
                ->setParameter('entreprise', $entreprise);
        }

        // Pagine sur la requete principale : les collections jointes ne faussent
        // pas le LIMIT ici car on limite en amont sur un jeu d'IDs distincts.
        $ids = array_column(
            (clone $qb)->select('DISTINCT o.id, o.datePublication')->getQuery()->getScalarResult(),
            'id'
        );

        if (empty($ids)) {
            return [];
        }

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
     * Pagine les offres d'une entreprise (factorise le QB duplique de
     * Entreprise\OffreController - audit P3).
     *
     * @return Offre[]
     */
    public function findByEntreprisePaginated(\App\Entity\Entreprise $entreprise, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.entreprise = :entreprise')
            ->setParameter('entreprise', $entreprise)
            ->orderBy('o.datePublication', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByEntreprise(\App\Entity\Entreprise $entreprise): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.entreprise = :entreprise')
            ->setParameter('entreprise', $entreprise)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Offres publiees d'une entreprise donnee, avec eager loading (audit C5) :
     * evite le `for offre in entreprise.offres` non pagine + filtre Twig.
     *
     * @return Offre[]
     */
    public function findPublishedByEntreprise(\App\Entity\Entreprise $entreprise): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.entreprise = :entreprise')
            ->andWhere('o.statut = :statut')
            ->setParameter('entreprise', $entreprise)
            ->setParameter('statut', StatutOffre::PUBLIEE)
            ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
            ->leftJoin('om.metier', 'm')->addSelect('m')
            ->orderBy('o.datePublication', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Incremente le compteur de vues de maniere atomique (audit P5).
     */
    public function incrementViewCount(int $id): void
    {
        $this->createQueryBuilder('o')
            ->update()
            ->set('o.views', 'o.views + 1')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    /**
     * Passe en "expiree" toutes les offres publiees dont la date d'expiration
     * est depassee, via un UPDATE DQL batch (pas de chargement en memoire - audit A3).
     *
     * @return int Nombre de lignes affectees
     */
    public function expireOutdated(\DateTimeImmutable $now): int
    {
        return $this->createQueryBuilder('o')
            ->update()
            ->set('o.statut', ':nouveauStatut')
            ->where('o.dateExpiration IS NOT NULL')
            ->andWhere('o.dateExpiration < :now')
            ->andWhere('o.statut = :statutActuel')
            ->setParameter('nouveauStatut', StatutOffre::EXPIREE)
            ->setParameter('now', $now)
            ->setParameter('statutActuel', StatutOffre::PUBLIEE)
            ->getQuery()
            ->execute();
    }

    /**
     * Offres similaires (meme metier, sinon meme ville), avec eager loading
     * pour eviter les lazy loads dans le template (audit A7).
     *
     * @return Offre[]
     */
    public function findSimilar(Offre $offre, int $limit = 3): array
    {
        $idsQb = $this->createQueryBuilder('o')
            ->select('DISTINCT o.id')
            ->where('o.statut = :statut')
            ->setParameter('statut', StatutOffre::PUBLIEE)
            ->andWhere('o.id != :id')
            ->setParameter('id', $offre->getId())
            ->setMaxResults($limit);

        $premierMetier = $offre->getOffreMetiers()->first() ?: null;
        $villes = $offre->getVilles();

        if ($premierMetier && $premierMetier->getMetier()) {
            $idsQb->leftJoin('o.offreMetiers', 'om')
                ->andWhere('om.metier = :metier')
                ->setParameter('metier', $premierMetier->getMetier());
        } elseif (!empty($villes)) {
            $idsQb->leftJoin('o.offreMetiers', 'om')
                ->andWhere('om.ville = :ville')
                ->setParameter('ville', $villes[0]);
        }

        $ids = array_column($idsQb->getQuery()->getScalarResult(), 'id');

        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
            ->leftJoin('om.metier', 'm')->addSelect('m')
            ->getQuery()
            ->getResult();
    }

    /**
     * Iterateur memoire-constant pour l'export CSV DAIP (audit C6) : traite les
     * offres par lots (batch) avec un EntityManager::clear() entre chaque lot,
     * pour ne jamais garder plus d'un batch en memoire.
     *
     * @return iterable<Offre>
     */
    public function iterateByFilters(array $criteres = [], int $batchSize = 200): iterable
    {
        $offset = 0;

        do {
            $ids = array_column(
                $this->baseQueryBuilderFilters($criteres)
                    ->select('DISTINCT o.id, o.datePublication')
                    ->orderBy('o.datePublication', 'DESC')
                    ->setFirstResult($offset)
                    ->setMaxResults($batchSize)
                    ->getQuery()
                    ->getScalarResult(),
                'id'
            );

            if (empty($ids)) {
                break;
            }

            $offres = $this->createQueryBuilder('o')
                ->where('o.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->leftJoin('o.entreprise', 'e')->addSelect('e')
                ->leftJoin('o.offreMetiers', 'om')->addSelect('om')
                ->leftJoin('om.metier', 'm')->addSelect('m')
                ->orderBy('o.datePublication', 'DESC')
                ->getQuery()
                ->getResult();

            foreach ($offres as $offre) {
                yield $offre;
            }

            $this->getEntityManager()->clear();
            $offset += $batchSize;
        } while (count($ids) === $batchSize);
    }

    /**
     * @return array<int, array{mois: string, total: int}>
     */
    public function getEvolutionParMois(): array
    {
        $sql = <<<SQL
            SELECT DATE_FORMAT(date_publication, '%Y-%m') AS mois, COUNT(*) AS total
            FROM offre
            WHERE statut = 'publiee'
            GROUP BY mois
            ORDER BY mois ASC
            LIMIT 12
        SQL;

        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        return array_map(static fn(array $row) => ['mois' => $row['mois'], 'total' => (int) $row['total']], $rows);
    }

    /**
     * @return array<int, array{nom: string, total: int}>
     */
    public function getRepartitionMetiers(): array
    {
        $sql = <<<SQL
            SELECT m.nom, COUNT(DISTINCT o.id) AS total
            FROM offre o
            JOIN offre_metier om ON om.offre_id = o.id
            JOIN metier m ON m.id = om.metier_id
            WHERE o.statut = 'publiee'
            GROUP BY m.id, m.nom
            ORDER BY total DESC
            LIMIT 10
        SQL;

        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        return array_map(static fn(array $row) => ['nom' => $row['nom'], 'total' => (int) $row['total']], $rows);
    }

    /**
     * @return array<int, array{ville: string, total: int}>
     */
    public function getRepartitionVilles(): array
    {
        $sql = <<<SQL
            SELECT om.ville, COUNT(DISTINCT o.id) AS total
            FROM offre o
            JOIN offre_metier om ON om.offre_id = o.id
            WHERE o.statut = 'publiee'
            GROUP BY om.ville
            ORDER BY total DESC
            LIMIT 10
        SQL;

        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        return array_map(static fn(array $row) => ['ville' => $row['ville'], 'total' => (int) $row['total']], $rows);
    }
}
