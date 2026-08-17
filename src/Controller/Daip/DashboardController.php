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

        $dernieresOffres = $offreRepository->findLatestWithRelations(limit: 5);

        return $this->render('daip/dashboard.html.twig', [
            'compteurs' => $compteurs,
            'totalOffres' => $totalOffres,
            'totalEntreprises' => $totalEntreprises,
            'dernieresOffres' => $dernieresOffres,
            'evolutionParMois' => $offreRepository->getEvolutionParMois(),
            'repartitionMetiers' => $offreRepository->getRepartitionMetiers(),
            'repartitionVilles' => $offreRepository->getRepartitionVilles(),
        ]);
    }
}
