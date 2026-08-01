<?php

namespace App\Controller\Daip;

use App\Repository\EntrepriseRepository;
use App\Repository\OffreRepository;
use App\Repository\SecteurRepository;
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
        SecteurRepository $secteurRepository,
    ): Response {
        $compteurs = $offreRepository->countByStatut();

        $totalOffres = array_sum($compteurs);
        $totalEntreprises = $entrepriseRepository->count([]);

        $dernieresOffres = $offreRepository->findBy([], ['datePublication' => 'DESC'], 5);

        $evolutionParMois = $this->getEvolutionParMois($offreRepository);
        $repartitionSecteurs = $this->getRepartitionSecteurs($offreRepository);
        $repartitionVilles = $this->getRepartitionVilles($offreRepository);

        return $this->render('daip/dashboard.html.twig', [
            'compteurs' => $compteurs,
            'totalOffres' => $totalOffres,
            'totalEntreprises' => $totalEntreprises,
            'dernieresOffres' => $dernieresOffres,
            'evolutionParMois' => $evolutionParMois,
            'repartitionSecteurs' => $repartitionSecteurs,
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
    private function getRepartitionSecteurs(OffreRepository $offreRepository): array
    {
        $sql = <<<SQL
            SELECT s.nom, COUNT(o.id) AS total
            FROM offre o
            JOIN secteur s ON o.secteur_id = s.id
            WHERE o.statut = 'publiee'
            GROUP BY s.id, s.nom
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
            SELECT ville, COUNT(*) AS total
            FROM offre
            WHERE statut = 'publiee'
            GROUP BY ville
            ORDER BY total DESC
            LIMIT 10
        SQL;

        $rows = $offreRepository->getEntityManager()->getConnection()->fetchAllAssociative($sql);

        return array_map(static function (array $row) {
            return ['ville' => $row['ville'], 'total' => (int) $row['total']];
        }, $rows);
    }
}
