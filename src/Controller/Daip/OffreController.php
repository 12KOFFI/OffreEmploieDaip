<?php

namespace App\Controller\Daip;

use App\Repository\OffreRepository;
use App\Repository\SecteurRepository;
use App\Repository\EntrepriseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
    public function exportCsv(Request $request, OffreRepository $offreRepository): Response
    {
        $filtres = [
            'statut' => $request->query->get('statut'),
            'secteur' => $request->query->get('secteur'),
            'ville' => $request->query->get('ville'),
            'dateDebut' => $request->query->get('dateDebut'),
            'dateFin' => $request->query->get('dateFin'),
        ];

        $offres = $offreRepository->findByFilters(array_filter($filtres));

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Référence', 'Titre', 'Entreprise', 'Ville', 'Secteur', 'Type', 'Statut', 'Date publication', 'Date expiration'], ';');

        foreach ($offres as $offre) {
            fputcsv($handle, [
                'DAIP-' . $offre->getDatePublication()?->format('Y') . '-' . str_pad((string) $offre->getId(), 5, '0', STR_PAD_LEFT),
                $offre->getTitre(),
                $offre->getEntreprise()?->getNom(),
                $offre->getVille(),
                $offre->getSecteur()?->getNom(),
                $offre->getTypeContrat()?->label(),
                $offre->getStatut()?->label(),
                $offre->getDatePublication()?->format('d/m/Y'),
                $offre->getDateExpiration()?->format('d/m/Y'),
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $response = new Response($csv);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'registre_offres_' . (new \DateTimeImmutable())->format('Ymd_His') . '.csv'
        );
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('', name: 'daip_offres_index', methods: ['GET'])]
    public function index(Request $request, OffreRepository $offreRepository, SecteurRepository $secteurRepository, EntrepriseRepository $entrepriseRepository): Response
    {
        $filtres = [
            'statut' => $request->query->get('statut'),
            'secteur' => $request->query->get('secteur'),
            'ville' => $request->query->get('ville'),
            'dateDebut' => $request->query->get('dateDebut'),
            'dateFin' => $request->query->get('dateFin'),
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $allOffres = $offreRepository->findByFilters(array_filter($filtres));
        $total = count($allOffres);
        $totalPages = max(1, (int) ceil($total / $limit));

        $page = min($page, $totalPages);

        $offres = array_slice($allOffres, $offset, $limit);

        $compteurs = $offreRepository->countByStatut();
        $totalEntreprises = $entrepriseRepository->count([]);

        if ($request->isXmlHttpRequest()) {
            return $this->render('daip/offres/_tableau.html.twig', [
                'offres' => $offres,
                'filtres' => $filtres,
                'secteurs' => $secteurRepository->findBy([], ['nom' => 'ASC']),
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
            'secteurs' => $secteurRepository->findBy([], ['nom' => 'ASC']),
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'compteurs' => $compteurs,
            'totalEntreprises' => $totalEntreprises,
        ]);
    }
}
