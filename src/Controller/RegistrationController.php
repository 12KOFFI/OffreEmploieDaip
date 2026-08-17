<?php

namespace App\Controller;

use App\Dto\RegistrationDto;
use App\Entity\Entreprise;
use App\Entity\User;
use App\Form\EntrepriseRegistrationType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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

        $dto = new RegistrationDto();

        $form = $this->createForm(EntrepriseRegistrationType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setEmail($dto->email);
            $user->setPassword($passwordHasher->hashPassword($user, $dto->plainPassword));
            $user->setRoles(['ROLE_ENTREPRISE']);

            $entreprise = new Entreprise();
            $entreprise->setNom($dto->nom);
            $entreprise->setContact($dto->contact);
            $entreprise->setContactResponsable($dto->contactResponsable);
            $entreprise->setAutreContact($dto->autreContact);
            $entreprise->setDescription($dto->description);
            $entreprise->setUser($user);
            $user->setEntreprise($entreprise);

            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Un compte existe déjà avec cet email.');

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $this->addFlash('success', 'Compte cree avec succes, vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
