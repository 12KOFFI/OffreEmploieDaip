<?php

namespace App\Controller\Entreprise;

use App\Entity\User;
use App\Form\EntrepriseProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entreprise/profil')]
#[IsGranted('ROLE_ENTREPRISE')]
class ProfilController extends AbstractController
{
    #[Route('', name: 'entreprise_profil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        if (!$entreprise) {
            throw $this->createAccessDeniedException("Ce compte n'a pas de profil entreprise associé.");
        }

        $form = $this->createForm(EntrepriseProfilType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('logo')->getData();

            if ($uploadedFile) {
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/entreprises';
                if (!is_dir($uploadsDirectory)) {
                    mkdir($uploadsDirectory, 0775, true);
                }
                $newFilename = bin2hex(random_bytes(16)) . '.' . ($uploadedFile->guessExtension() ?? 'bin');
                $uploadedFile->move($uploadsDirectory, $newFilename);
                $entreprise->setLogo('/uploads/entreprises/' . $newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('entreprise_profil_edit');
        }

        return $this->render('entreprise/profil/edit.html.twig', [
            'form' => $form,
            'entreprise' => $entreprise,
        ]);
    }
}
