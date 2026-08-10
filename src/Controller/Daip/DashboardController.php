<?php

namespace App\Controller\Daip;

use App\Repository\EntrepriseRepository;
use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/daip')]
#[IsGranted('ROLE_DAIP')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'daip_dashboard', methods: ['GET'])]
    public function index(
        OffreRepository $offreRepository,
        EntrepriseRepository $entrepriseRepository,
    ): Response {
        $compteurs = $offreRepository->countByStatut();

        $totalOffres = array_sum($compteurs);
        $totalEntreprises = $entrepriseRepository->count([]);

        $dernieresOffres = $offreRepository->findBy([], ['datePublication' => 'DESC'], 5);

        $evolutionParMois = $this->getEvolutionParMois($offreRepository);
        $repartitionMetiers = $this->getRepartitionMetiers($offreRepository);
        $repartitionVilles = $this->getRepartitionVilles($offreRepository);

        return $this->render('daip/dashboard.html.twig', [
            'compteurs' => $compteurs,
            'totalOffres' => $totalOffres,
            'totalEntreprises' => $totalEntreprises,
            'dernieresOffres' => $dernieresOffres,
            'evolutionParMois' => $evolutionParMois,
            'repartitionMetiers' => $repartitionMetiers,
            'repartitionVilles' => $repartitionVilles,
        ]);
    }

    /**
     * @return array<int, array{mois: string, total: int}>
     */
    private function getEvolutionParMois(OffreRepository $offreRepository): array
    {
        $sql = <<<SQL
            SELECT DATE_FORMAT(date_publication, '%Y-%m') AS mois, COUNT(*) AS total
            FROM offre
            WHERE statut = 'publiee'
            GROUP BY mois
            ORDER BY mois ASC
            LIMIT 12
        SQL;

        $rows = $offreRepository->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        return array_map(static function (array $row) {
            return ['mois' => $row['mois'], 'total' => (int) $row['total']];
        }, $rows);
    }

    /**
     * @return array<int, array{nom: string, total: int}>
     */
    private function getRepartitionMetiers(OffreRepository $offreRepository): array
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

        $rows = $offreRepository->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        return array_map(static function (array $row) {
            return ['nom' => $row['nom'], 'total' => (int) $row['total']];
        }, $rows);
    }

    /**
     * @return array<int, array{ville: string, total: int}>
     */
    private function getRepartitionVilles(OffreRepository $offreRepository): array
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

        $rows = $offreRepository->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        return array_map(static function (array $row) {
            return ['ville' => $row['ville'], 'total' => (int) $row['total']];
        }, $rows);
    }
}
