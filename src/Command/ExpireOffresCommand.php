<?php

namespace App\Command;

use App\Enum\StatutOffre;
use App\Repository\OffreRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:expire-offres',
    description: 'Passe les offres dont la date d\'expiration est dépassée en statut "expiree".',
)]
class ExpireOffresCommand extends Command
{
    public function __construct(
        private readonly OffreRepository $offreRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $now = new \DateTimeImmutable();
        $offresExpirees = $this->offreRepository->createQueryBuilder('o')
            ->where('o.dateExpiration IS NOT NULL')
            ->andWhere('o.dateExpiration < :now')
            ->andWhere('o.statut = :statut')
            ->setParameter('now', $now)
            ->setParameter('statut', StatutOffre::PUBLIEE)
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($offresExpirees as $offre) {
            $offre->setStatut(StatutOffre::EXPIREE);
            $this->entityManager->persist($offre);
            $count++;
        }

        if ($count > 0) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('%d offre(s) expirée(s) ont été mises à jour.', $count));

        return Command::SUCCESS;
    }
}