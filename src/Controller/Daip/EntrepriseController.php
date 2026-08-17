<?php

namespace App\Controller\Daip;

use App\Entity\Entreprise;
use App\Repository\EntrepriseRepository;
use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/daip/entreprises', name: 'daip_entreprises_')]
#[IsGranted('ROLE_DAIP')]
class EntrepriseController extends AbstractController
{
    private const PAR_PAGE = 12;

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, EntrepriseRepository $entrepriseRepository): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $page = max(1, $request->query->getInt('page', 1));

        $resultat = $entrepriseRepository->searchPaginated($search, $page, self::PAR_PAGE);

        return $this->render('daip/entreprises/index.html.twig', [
            'entreprises' => $resultat['entreprises'],
            'total' => $resultat['total'],
            'totalPages' => $resultat['totalPages'],
            'page' => $resultat['page'],
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Entreprise $entreprise, OffreRepository $offreRepository): Response
    {
        return $this->render('daip/entreprises/show.html.twig', [
            'entreprise' => $entreprise,
            'offresPubliees' => $offreRepository->findPublishedByEntreprise($entreprise),
        ]);
    }
}
