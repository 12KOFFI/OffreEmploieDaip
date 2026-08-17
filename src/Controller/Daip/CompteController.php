<?php

namespace App\Controller\Daip;

use App\Dto\CreateDaipDto;
use App\Entity\User;
use App\Form\CreateDaipType;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Comme convenu : seul un compte DAIP deja existant peut en creer un
 * nouveau (formulaire protege). Le tout premier compte DAIP se cree via
 * la commande console app:create-daip, cf README.
 */
#[Route('/daip/comptes')]
#[IsGranted('ROLE_DAIP_ADMIN')]
class CompteController extends AbstractController
{
    #[Route('', name: 'daip_comptes_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $comptes = $userRepository->findAllDaip();

        return $this->render('daip/comptes/index.html.twig', [
            'comptes' => $comptes,
        ]);
    }

    #[Route('/nouveau', name: 'daip_comptes_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $dto = new CreateDaipDto();
        $form = $this->createForm(CreateDaipType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setEmail($dto->email);
            $user->setPassword($passwordHasher->hashPassword($user, $dto->plainPassword));
            $user->setRoles(['ROLE_DAIP']);

            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', 'Un compte existe déjà avec cet email.');

                return $this->render('daip/comptes/new.html.twig', [
                    'form' => $form,
                ]);
            }

            $this->addFlash('success', 'Nouveau compte DAIP créé pour ' . $user->getEmail() . '.');

            return $this->redirectToRoute('daip_comptes_index');
        }

        return $this->render('daip/comptes/new.html.twig', [
            'form' => $form,
        ]);
    }
}
