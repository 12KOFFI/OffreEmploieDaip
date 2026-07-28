<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\User;
use App\Form\EntrepriseRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard_redirect');
        }

        $user = new User();
        $user->setEntreprise(new Entreprise());

        $form = $this->createForm(EntrepriseRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Un email deja utilise ? On evite le conflit UniqueEntity ici,
            // ideal serait d'ajouter la contrainte #[UniqueEntity] sur User
            $existing = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $user->getEmail()]);

            if ($existing) {
                $this->addFlash('error', 'Un compte existe deja avec cet email.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_ENTREPRISE']);

            // Relie l'entreprise a son user (relation bidirectionnelle)
            $user->getEntreprise()->setUser($user);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Compte cree avec succes, vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
