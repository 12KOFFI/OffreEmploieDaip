<?php

namespace App\Controller\Daip;

use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    #[Route('', name: 'daip_offres_index', methods: ['GET'])]
    public function index(Request $request, OffreRepository $offreRepository): Response
    {
        $statutFiltre = $request->query->get('statut');

        $criteres = [];
        if ($statutFiltre) {
            $criteres['statut'] = $statutFiltre;
        }

        $offres = $offreRepository->findBy($criteres, ['datePublication' => 'DESC']);

        return $this->render('daip/offres/index.html.twig', [
            'offres' => $offres,
            'statutFiltre' => $statutFiltre,
        ]);
    }
}
