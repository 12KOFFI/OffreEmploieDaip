<?php

namespace App\Controller\Entreprise;

use App\Entity\Offre;
use App\Entity\User;
use App\Enum\StatutOffre;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use App\Security\OffreVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entreprise/offres')]
#[IsGranted('ROLE_ENTREPRISE')]
class OffreController extends AbstractController
{
    #[Route('', name: 'entreprise_offres_index', methods: ['GET'])]
    public function index(OffreRepository $offreRepository): Response
    {
        $entreprise = $this->getEntrepriseOrThrow();

        $offres = $offreRepository->findBy(
            ['entreprise' => $entreprise],
            ['datePublication' => 'DESC'],
        );

        return $this->render('entreprise/offres/index.html.twig', [
            'offres' => $offres,
        ]);
    }

    #[Route('/nouvelle', name: 'entreprise_offres_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entreprise = $this->getEntrepriseOrThrow();

        $offre = new Offre();
        $offre->setEntreprise($entreprise);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offre);
            $entityManager->flush();

            $this->addFlash('success', 'Offre enregistrée en brouillon.');

            return $this->redirectToRoute('entreprise_offres_index');
        }

        return $this->render('entreprise/offres/form.html.twig', [
            'form' => $form,
            'offre' => $offre,
            'mode' => 'creation',
        ]);
    }

    #[Route('/{id}/modifier', name: 'entreprise_offres_edit', methods: ['GET', 'POST'])]
    public function edit(Offre $offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::EDIT, $offre);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Offre mise à jour.');

            return $this->redirectToRoute('entreprise_offres_index');
        }

        return $this->render('entreprise/offres/form.html.twig', [
            'form' => $form,
            'offre' => $offre,
            'mode' => 'edition',
        ]);
    }

    #[Route('/{id}/supprimer', name: 'entreprise_offres_delete', methods: ['POST'])]
    public function delete(Offre $offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::DELETE, $offre);

        if ($this->isCsrfTokenValid('supprimer-offre-' . $offre->getId(), $request->request->get('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();
            $this->addFlash('success', 'Offre supprimée.');
        }

        return $this->redirectToRoute('entreprise_offres_index');
    }

    #[Route('/{id}/publier', name: 'entreprise_offres_publier', methods: ['POST'])]
    public function publier(Offre $offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::CHANGE_STATUT, $offre);

        if ($this->isCsrfTokenValid('changer-statut-' . $offre->getId(), $request->request->get('_token'))) {
            $offre->setStatut(StatutOffre::PUBLIEE);
            $offre->setDatePublication(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Offre publiée, elle est désormais visible publiquement.');
        }

        return $this->redirectToRoute('entreprise_offres_index');
    }

    #[Route('/{id}/retirer', name: 'entreprise_offres_retirer', methods: ['POST'])]
    public function retirer(Offre $offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::CHANGE_STATUT, $offre);

        if ($this->isCsrfTokenValid('changer-statut-' . $offre->getId(), $request->request->get('_token'))) {
            $offre->setStatut(StatutOffre::RETIREE);
            $entityManager->flush();
            $this->addFlash('success', 'Offre retirée du registre public.');
        }

        return $this->redirectToRoute('entreprise_offres_index');
    }

    private function getEntrepriseOrThrow(): \App\Entity\Entreprise
    {
        /** @var User $user */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        if (!$entreprise) {
            throw $this->createAccessDeniedException("Ce compte n'a pas de profil entreprise associé.");
        }

        return $entreprise;
    }
}
