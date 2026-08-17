<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Enum\StatutOffre;
use App\Enum\TypeContrat;
use App\Repository\OffreRepository;
use App\Repository\MetierRepository;
use App\Security\OffreVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OffreController extends AbstractController
{
    private const PAR_PAGE = 12;

    /**
     * Liste publique complete des offres publiees, avec filtres + pagination.
     */
    #[Route('/offres', name: 'app_offres_liste', methods: ['GET'])]
    public function liste(Request $request, OffreRepository $offreRepository, MetierRepository $metierRepository): Response
    {
        $filtres = [
            'q' => $request->query->get('q', ''),
            'ville' => $request->query->get('ville', ''),
            'metier' => $request->query->get('metier', ''),
            'typeContrat' => $request->query->get('typeContrat', ''),
        ];
        $filtresActifs = array_filter($filtres) !== [];

        $page = max(1, $request->query->getInt('page', 1));
        $total = $offreRepository->compterOffresPubliees($filtres);
        $totalPages = max(1, (int) ceil($total / self::PAR_PAGE));
        $page = min($page, $totalPages);

        $offres = $offreRepository->rechercherOffresPubliees(
            $filtres,
            self::PAR_PAGE,
            ($page - 1) * self::PAR_PAGE,
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('offres/_liste.html.twig', [
                'offres' => $offres,
                'filtres' => $filtres,
                'filtresActifs' => $filtresActifs,
                'metiers' => $metierRepository->findBy([], ['nom' => 'ASC']),
                'typesContrat' => TypeContrat::cases(),
                'total' => $total,
                'page' => $page,
                'totalPages' => $totalPages,
            ]);
        }

        return $this->render('offres/liste.html.twig', [
            'offres' => $offres,
            'filtres' => $filtres,
            'filtresActifs' => $filtresActifs,
            'metiers' => $metierRepository->findBy([], ['nom' => 'ASC']),
            'typesContrat' => TypeContrat::cases(),
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Page de detail publique d'une offre.
     * Le OffreVoter autorise : tout le monde si l'offre est publiee,
     * sinon uniquement l'entreprise proprietaire ou la DAIP (utile pour
     * previsualiser un brouillon avant publication, ou verifier une offre
     * retiree).
     */
    #[Route('/offres/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Offre $offre, OffreRepository $offreRepository): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::VIEW, $offre);

        if ($offre->getStatut() === StatutOffre::PUBLIEE) {
            $offreRepository->incrementViewCount($offre->getId());
        }

        return $this->render('offres/show.html.twig', [
            'offre' => $offre,
            'offres_similaires' => $offreRepository->findSimilar($offre),
        ]);
    }
}
