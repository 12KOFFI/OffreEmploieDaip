<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-daip',
    description: 'Cree un compte DAIP admin (ROLE_DAIP_ADMIN). A utiliser pour le tout premier compte, ensuite ca passe par le formulaire admin protege.',
)]
class CreateDaipCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, "Email du compte DAIP admin")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');

        // Saisie interactive masquee pour ne pas exposer le mot de passe dans
        // l'historique bash ni dans `ps aux`
        $plainPassword = $io->askHidden('Mot de passe du compte DAIP admin');
        if (!$plainPassword || strlen($plainPassword) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caracteres.');
            return Command::FAILURE;
        }

        $existing = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existing) {
            $io->error(sprintf('Un compte existe deja avec l\'email "%s".', $email));
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_DAIP_ADMIN']); // Admin = peut creer d'autres comptes DAIP
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Compte DAIP admin cree pour "%s".', $email));

        return Command::SUCCESS;
    }
}
