<?php

namespace App\Controller\Daip;

use App\Repository\OffreRepository;
use App\Repository\MetierRepository;
use App\Repository\EntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/daip/offres')]
#[IsGranted('ROLE_DAIP')]
class OffreController extends AbstractController
{
    /**
     * Vue de supervision, en lecture seule : la DAIP voit TOUTES les
     * offres de TOUTES les entreprises, mais ne peut rien modifier.
     * Aucune route d'edition/suppression/changement de statut n'existe
     * dans ce contrôleur - c'est une garantie architecturale, pas
     * seulement une question d'UI.
     */
    #[Route('/export/csv', name: 'daip_offres_export_csv', methods: ['GET'])]
    public function exportCsv(Request $request, OffreRepository $offreRepository): StreamedResponse
    {
        $filtres = [
            'statut' => $request->query->get('statut'),
            'metier' => $request->query->get('metier'),
            'ville' => $request->query->get('ville'),
            'dateDebut' => $request->query->get('dateDebut'),
            'dateFin' => $request->query->get('dateFin'),
        ];
        $filtres = array_filter($filtres);

        $response = new StreamedResponse(function () use ($offreRepository, $filtres): void {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM pour Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'Référence Offre',
                'Titre de l\'offre',
                'Entreprise',
                'Statut',
                'Date publication',
                'Date expiration',
                'Métier / Poste',
                'Ville',
                'Type de contrat',
                'Nombre de postes',
                'Niveau d\'étude',
                'Diplôme',
                'Expérience (années)',
                'Salaire Min (FCFA)',
                'Salaire Max (FCFA)'
            ], ';');

            // Iteration par lots memoire-constante : jamais plus d'un batch d'offres
            // en memoire, meme sur un registre de plusieurs dizaines de milliers d'offres (audit C6).
            foreach ($offreRepository->iterateByFilters($filtres) as $offre) {
                $baseRow = [
                    'DAIP-' . $offre->getDatePublication()?->format('Y') . '-' . str_pad((string) $offre->getId(), 5, '0', STR_PAD_LEFT),
                    $offre->getTitre(),
                    $offre->getEntreprise()?->getNom(),
                    $offre->getStatut()?->label(),
                    $offre->getDatePublication()?->format('d/m/Y'),
                    $offre->getDateExpiration()?->format('d/m/Y'),
                ];

                $offreMetiers = $offre->getOffreMetiers();

                if ($offreMetiers->isEmpty()) {
                    fputcsv($handle, array_merge($baseRow, array_fill(0, 9, '')), ';');
                } else {
                    foreach ($offreMetiers as $om) {
                        $row = $baseRow;
                        $row[] = $om->getMetier()?->getNom();
                        $row[] = $om->getVille();
                        $row[] = $om->getTypeContrat()?->label();
                        $row[] = $om->getNombrePostes();
                        $row[] = $om->getNiveauEtude()?->label();
                        $row[] = $om->getDiplome()?->label();
                        $row[] = $om->getNbAnneesExperience();
                        $row[] = $om->getSalaireMin();
                        $row[] = $om->getSalaireMax();

                        fputcsv($handle, $row, ';');
                    }
                }
            }

            fclose($handle);
        });

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'registre_offres_' . (new \DateTimeImmutable())->format('Ymd_His') . '.csv'
        );
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('', name: 'daip_offres_index', methods: ['GET'])]
    public function index(Request $request, OffreRepository $offreRepository, MetierRepository $metierRepository, EntrepriseRepository $entrepriseRepository): Response
    {
        $filtres = [
            'statut' => $request->query->get('statut'),
            'metier' => $request->query->get('metier'),
            'ville' => $request->query->get('ville'),
            'dateDebut' => $request->query->get('dateDebut'),
            'dateFin' => $request->query->get('dateFin'),
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 9;

        // Pagination directement en BDD pour eviter de charger toutes les offres en memoire
        $total = $offreRepository->countByFilters(array_filter($filtres));
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $offres = $offreRepository->findByFiltersPaginated(array_filter($filtres), $limit, $offset);

        $compteurs = $offreRepository->countByStatut();
        $totalEntreprises = $entrepriseRepository->count([]);

        if ($request->isXmlHttpRequest()) {
            return $this->render('daip/offres/_tableau.html.twig', [
                'offres' => $offres,
                'filtres' => $filtres,
                'metiers' => $metierRepository->findBy([], ['nom' => 'ASC']),
                'page' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'compteurs' => $compteurs,
                'totalEntreprises' => $totalEntreprises,
            ]);
        }

        return $this->render('daip/offres/index.html.twig', [
            'offres' => $offres,
            'filtres' => $filtres,
            'metiers' => $metierRepository->findBy([], ['nom' => 'ASC']),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'compteurs' => $compteurs,
            'totalEntreprises' => $totalEntreprises,
        ]);
    }
}
