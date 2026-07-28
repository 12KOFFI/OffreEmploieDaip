<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Security\OffreVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OffreController extends AbstractController
{
    /**
     * Page de detail publique d'une offre.
     * Le OffreVoter autorise : tout le monde si l'offre est publiee,
     * sinon uniquement l'entreprise proprietaire ou la DAIP (utile pour
     * previsualiser un brouillon avant publication, ou verifier une offre
     * retiree).
     */
    #[Route('/offres/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::VIEW, $offre);

        return $this->render('offres/show.html.twig', [
            'offre' => $offre,
        ]);
    }
}
