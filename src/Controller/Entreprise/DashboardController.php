<?php

namespace App\Controller\Entreprise;

use App\Entity\User;
use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entreprise')]
#[IsGranted('ROLE_ENTREPRISE')]
class DashboardController extends AbstractController
{
    use \App\Security\EntrepriseContextTrait;

    #[Route('/dashboard', name: 'entreprise_dashboard', methods: ['GET'])]
    public function index(OffreRepository $offreRepository): Response
    {
        $entreprise = $this->getEntrepriseOrThrow();

        $compteurs = $offreRepository->countByStatutForEntreprise($entreprise);

        $totalOffres = array_sum($compteurs);

        $dernieresOffres = $offreRepository->findLatestWithRelations($entreprise, 5);

        return $this->render('entreprise/dashboard.html.twig', [
            'entreprise' => $entreprise,
            'compteurs' => $compteurs,
            'totalOffres' => $totalOffres,
            'dernieresOffres' => $dernieresOffres,
        ]);
    }
}
