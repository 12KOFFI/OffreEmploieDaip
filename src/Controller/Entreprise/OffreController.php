<?php

namespace App\Controller\Entreprise;

use App\Entity\Offre;
use App\Entity\OffreMetier;
use App\Entity\Metier;
use App\Repository\MetierRepository;
use App\Entity\User;
use App\Enum\StatutOffre;
use App\Form\OffreType;
use App\Repository\OffreRepository;
use App\Security\OffreVoter;
use App\Service\OffreManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entreprise/offres')]
#[IsGranted('ROLE_ENTREPRISE')]
class OffreController extends AbstractController
{
    use \App\Security\EntrepriseContextTrait;
    #[Route('', name: 'entreprise_offres_index', methods: ['GET'])]
    public function index(OffreRepository $offreRepository, Request $request): Response
    {
        $entreprise = $this->getEntrepriseOrThrow();

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $qb = $offreRepository->createQueryBuilder('o')
            ->where('o.entreprise = :entreprise')
            ->setParameter('entreprise', $entreprise)
            ->orderBy('o.datePublication', 'DESC');

        $total = (int) $qb->select('COUNT(o.id)')->getQuery()->getSingleScalarResult();
        $totalPages = max(1, (int) ceil($total / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $offres = $offreRepository->createQueryBuilder('o')
            ->where('o.entreprise = :entreprise')
            ->setParameter('entreprise', $entreprise)
            ->orderBy('o.datePublication', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $compteurs = $offreRepository->countByStatutForEntreprise($entreprise);

        return $this->render('entreprise/offres/index.html.twig', [
            'offres' => $offres,
            'compteurs' => $compteurs,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    #[Route('/nouvelle', name: 'entreprise_offres_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OffreManager $offreManager, EntityManagerInterface $entityManager, MetierRepository $metierRepository): Response
    {
        $entreprise = $this->getEntrepriseOrThrow();

        $offre = new Offre();
        $offre->setEntreprise($entreprise);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->resolveCustomMetiers($form, $metierRepository, $entityManager);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processImageUpload($offre, $form);

            $offreManager->creerBrouillon($offre);

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
    public function edit(Offre $offre, Request $request, OffreManager $offreManager, EntityManagerInterface $entityManager, MetierRepository $metierRepository): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::EDIT, $offre);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->resolveCustomMetiers($form, $metierRepository, $entityManager);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processImageUpload($offre, $form);

            $offreManager->modifier($offre);

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
    public function delete(Offre $offre, Request $request, OffreManager $offreManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::DELETE, $offre);

        if ($this->isCsrfTokenValid('supprimer-offre-' . $offre->getId(), $request->request->get('_token'))) {
            $offreManager->supprimer($offre);
            $this->addFlash('success', 'Offre supprimée.');
        }

        return $this->redirectToRoute('entreprise_offres_index');
    }

    #[Route('/{id}/publier', name: 'entreprise_offres_publier', methods: ['POST'])]
    public function publier(Offre $offre, Request $request, OffreManager $offreManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::CHANGE_STATUT, $offre);

        if ($this->isCsrfTokenValid('changer-statut-' . $offre->getId(), $request->request->get('_token'))) {
            $offreManager->publier($offre);
            $this->addFlash('success', 'Offre publiée, elle est désormais visible publiquement.');
        }

        return $this->redirectToRoute('entreprise_offres_index');
    }

    #[Route('/{id}/retirer', name: 'entreprise_offres_retirer', methods: ['POST'])]
    public function retirer(Offre $offre, Request $request, OffreManager $offreManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::CHANGE_STATUT, $offre);

        if ($this->isCsrfTokenValid('changer-statut-' . $offre->getId(), $request->request->get('_token'))) {
            $offreManager->retirer($offre);
            $this->addFlash('success', 'Offre retirée du registre public.');
        }

        return $this->redirectToRoute('entreprise_offres_index');
    }

    private function resolveCustomMetiers(FormInterface $form, MetierRepository $metierRepository, EntityManagerInterface $entityManager): void
    {
        foreach ($form->get('offreMetiers') as $offreMetierForm) {
            $offreMetier = $offreMetierForm->getData();
            $selection = $offreMetierForm->get('metier')->getData();
            if ($selection && $selection->getId() !== null) { continue; }
            $nom = trim((string) $offreMetierForm->get('autreMetier')->getData());
            $nom = preg_replace('/\\s+/', ' ', $nom) ?? $nom;
            if ($nom === '' || mb_strlen($nom) < 2 || mb_strlen($nom) > 150) {
                $offreMetierForm->get('metier')->addError(new FormError('Sélectionnez un métier ou saisissez un nom valide (2 à 150 caractères).'));
                continue;
            }
            $metier = $metierRepository->findOneByNameInsensitive($nom);
            if (!$metier) { $metier = (new Metier())->setNom($nom); $entityManager->persist($metier); }
            $offreMetier->setMetier($metier);
        }
    }

    private function processImageUpload(Offre $offre, \Symfony\Component\Form\FormInterface $form): void
    {
        $uploadedFile = $form->get('imageFile')->getData();

        if (!$uploadedFile) {
            return;
        }

        $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/offres';

        if (!is_dir($uploadsDirectory)) {
            mkdir($uploadsDirectory, 0775, true);
        }

        $newFilename = bin2hex(random_bytes(16)) . '.' . ($uploadedFile->guessExtension() ?? 'bin');
        $uploadedFile->move($uploadsDirectory, $newFilename);

        $offre->setImage('/uploads/offres/' . $newFilename);
    }
}

