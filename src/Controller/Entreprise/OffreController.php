<?php

namespace App\Controller\Entreprise;

use App\Entity\Competence;
use App\Entity\Offre;
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
    public function new(Request $request, OffreManager $offreManager, EntityManagerInterface $entityManager): Response
    {
        $entreprise = $this->getEntrepriseOrThrow();

        $offre = new Offre();
        $offre->setEntreprise($entreprise);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processImageUpload($offre, $form);
            $this->processCompetences($offre, $request, $entityManager);

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
    public function edit(Offre $offre, Request $request, OffreManager $offreManager, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::EDIT, $offre);

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processImageUpload($offre, $form);
            $this->processCompetences($offre, $request, $entityManager);

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

    #[Route('/{id}/dupliquer', name: 'entreprise_offres_duplicate', methods: ['POST'])]
    public function duplicate(Offre $offre, Request $request, OffreManager $offreManager): Response
    {
        $this->denyAccessUnlessGranted(OffreVoter::EDIT, $offre);

        if (!$this->isCsrfTokenValid('dupliquer-offre-' . $offre->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $entreprise = $this->getEntrepriseOrThrow();

        $copie = new Offre();
        $copie->setEntreprise($entreprise);
        $copie->setTitre($offre->getTitre() . ' (copie)');
        $copie->setDescription($offre->getDescription());
        $copie->setTypeContrat($offre->getTypeContrat());
        $copie->setVille($offre->getVille());
        $copie->setSalaireMin($offre->getSalaireMin());
        $copie->setSalaireMax($offre->getSalaireMax());
        $copie->setNbAnneesExperience($offre->getNbAnneesExperience());
        $copie->setNiveauEtude($offre->getNiveauEtude());
        $copie->setSecteur($offre->getSecteur());
        $copie->setImage($offre->getImage());

        foreach ($offre->getCompetences() as $competence) {
            $copie->addCompetence($competence);
        }

        $this->getDoctrine()->getManager()->persist($copie);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'Offre dupliquée avec succès. Vous pouvez maintenant la modifier.');

        return $this->redirectToRoute('entreprise_offres_edit', ['id' => $copie->getId()]);
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

        $newFilename = uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move($uploadsDirectory, $newFilename);

        $offre->setImage('/uploads/offres/' . $newFilename);
    }

    private function processCompetences(Offre $offre, Request $request, EntityManagerInterface $entityManager): void
    {
        $ids = $request->request->all('competences', []);
        if (!is_array($ids)) {
            return;
        }

        $offre->getCompetences()->clear();

        foreach ($ids as $id) {
            $competence = $entityManager->getRepository(Competence::class)->find($id);
            if ($competence) {
                $offre->addCompetence($competence);
            }
        }
    }

    #[Route('/api/competences/search', name: 'entreprise_api_competences_search', methods: ['GET'])]
    public function searchCompetences(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));

        if (strlen($q) < 1) {
            return $this->json([]);
        }

        $results = $entityManager->getConnection()->fetchAllAssociative(
            'SELECT id, nom FROM competence WHERE nom LIKE :q ORDER BY nom ASC LIMIT 20',
            ['q' => '%' . $q . '%']
        );

        return $this->json($results);
    }

    #[Route('/api/competences/create', name: 'entreprise_api_competences_create', methods: ['POST'])]
    public function createCompetence(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $nom = trim((string) $request->request->get('nom', ''));

        if (strlen($nom) < 1) {
            return $this->json(['error' => 'Nom de compétence requis.'], 400);
        }

        $existing = $entityManager->getConnection()->fetchOne(
            'SELECT id FROM competence WHERE nom = :nom',
            ['nom' => $nom]
        );

        if ($existing) {
            return $this->json(['id' => (int) $existing['id'], 'nom' => $nom]);
        }

        $entityManager->getConnection()->insert('competence', ['nom' => $nom]);

        $id = (int) $entityManager->getConnection()->lastInsertId();

        return $this->json(['id' => $id, 'nom' => $nom], 201);
    }
}

