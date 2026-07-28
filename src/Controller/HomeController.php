<?php

namespace App\Controller;

use App\Enum\TypeContrat;
use App\Repository\OffreRepository;
use App\Repository\SecteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        OffreRepository $offreRepository,
        SecteurRepository $secteurRepository,
    ): Response {
        $filtres = [
            'q' => $request->query->get('q', ''),
            'ville' => $request->query->get('ville', ''),
            'secteur' => $request->query->get('secteur', ''),
            'typeContrat' => $request->query->get('typeContrat', ''),
        ];

        // Un filtre est "actif" des qu'un des champs ci-dessus est renseigne
        $filtresActifs = array_filter($filtres) !== [];

        $offres = $offreRepository->rechercherOffresPubliees($filtres);

        return $this->render('home/index.html.twig', [
            'offres' => $offres,
            'filtres' => $filtres,
            'filtresActifs' => $filtresActifs,
            'secteurs' => $secteurRepository->findBy([], ['nom' => 'ASC']),
            'typesContrat' => TypeContrat::cases(),
        ]);
    }
}
