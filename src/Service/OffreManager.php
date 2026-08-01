<?php

namespace App\Service;

use App\Entity\Offre;
use App\Enum\StatutOffre;
use Doctrine\ORM\EntityManagerInterface;

class OffreManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function publier(Offre $offre): void
    {
        $offre->setStatut(StatutOffre::PUBLIEE);
        $offre->setDatePublication(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function retirer(Offre $offre): void
    {
        $offre->setStatut(StatutOffre::RETIREE);
        $this->entityManager->flush();
    }

    public function creerBrouillon(Offre $offre): void
    {
        // On pourrait ajouter d'autres logiques métier ici (ex: notifier un admin)
        $this->entityManager->persist($offre);
        $this->entityManager->flush();
    }

    public function modifier(Offre $offre): void
    {
        $this->entityManager->flush();
    }

    public function supprimer(Offre $offre): void
    {
        $this->entityManager->remove($offre);
        $this->entityManager->flush();
    }
}
