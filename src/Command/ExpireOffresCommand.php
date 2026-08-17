<?php

namespace App\Command;

use App\Repository\OffreRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:expire-offres',
    description: 'Passe les offres dont la date d\'expiration est dépassée en statut "expiree".',
)]
class ExpireOffresCommand extends Command
{
    public function __construct(
        private readonly OffreRepository $offreRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->offreRepository->expireOutdated(new \DateTimeImmutable());

        $io->success(sprintf('%d offre(s) expirée(s) ont été mises à jour.', $count));

        return Command::SUCCESS;
    }
}
