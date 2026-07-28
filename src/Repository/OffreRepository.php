<?php

namespace App\Repository;

use App\Entity\Offre;
use App\Enum\StatutOffre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * Recherche publique : uniquement les offres publiees, avec filtres optionnels.
     *
     * @param array{q?: string, ville?: string, secteur?: int, typeContrat?: string} $criteres
     * @return Offre[]
     */
    public function rechercherOffresPubliees(array $criteres = []): array
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.statut = :statut')
            ->setParameter('statut', StatutOffre::PUBLIEE)
            ->leftJoin('o.entreprise', 'e')->addSelect('e')
            ->leftJoin('o.secteur', 's')->addSelect('s')
            ->orderBy('o.datePublication', 'DESC');

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

        return $qb->getQuery()->getResult();
    }
}
