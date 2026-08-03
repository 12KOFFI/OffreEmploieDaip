<?php

namespace App\Security;

use App\Entity\Offre;
use App\Entity\User;
use App\Enum\StatutOffre;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OffreVoter extends Voter
{
    public const VIEW = 'OFFRE_VIEW';
    public const EDIT = 'OFFRE_EDIT';
    public const DELETE = 'OFFRE_DELETE';
    public const CHANGE_STATUT = 'OFFRE_CHANGE_STATUT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Offre
            && in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CHANGE_STATUT], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        /** @var Offre $offre */
        $offre = $subject;

        return match ($attribute) {
            // Tout le monde peut voir une offre publiee ; le proprietaire et
            // la DAIP peuvent voir une offre quel que soit son statut.
            self::VIEW => $offre->getStatut() === StatutOffre::PUBLIEE
                || $this->estProprietaire($offre, $user)
                || $this->estDaip($user),

            // EDIT / DELETE / CHANGE_STATUT : reserves a l'entreprise
            // proprietaire, JAMAIS a la DAIP (lecture seule, voir README).
            self::EDIT, self::DELETE, self::CHANGE_STATUT => $this->estProprietaire($offre, $user),

            default => false,
        };
    }

    private function estProprietaire(Offre $offre, mixed $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        $entreprise = $user->getEntreprise();

        return $entreprise !== null
            && $offre->getEntreprise() !== null
            && $offre->getEntreprise()->getId() === $entreprise->getId();
    }

    private function estDaip(mixed $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        $roles = $user->getRoles();
        return in_array('ROLE_DAIP', $roles, true) || in_array('ROLE_DAIP_ADMIN', $roles, true);
    }
}
