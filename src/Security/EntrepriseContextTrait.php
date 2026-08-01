<?php

namespace App\Security;

use App\Entity\Entreprise;
use App\Entity\User;

trait EntrepriseContextTrait
{
    /**
     * Récupère l'entreprise liée à l'utilisateur connecté ou lève une exception.
     */
    protected function getEntrepriseOrThrow(): Entreprise
    {
        /** @var User $user */
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        if (!$entreprise) {
            throw $this->createAccessDeniedException("Ce compte n'a pas de profil entreprise associé.");
        }

        return $entreprise;
    }
}
