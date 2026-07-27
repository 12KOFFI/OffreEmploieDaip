<?php

namespace App\Enum;

enum StatutOffre: string
{
    case BROUILLON = 'brouillon';
    case EN_ATTENTE = 'en_attente';
    case PUBLIEE = 'publiee';
    case REJETEE = 'rejetee';
    case RETIREE = 'retiree';
    case EXPIREE = 'expiree';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::EN_ATTENTE => 'En attente de validation DAIP',
            self::PUBLIEE => 'Publiee',
            self::REJETEE => 'Rejetee',
            self::RETIREE => 'Retiree par la DAIP',
            self::EXPIREE => 'Expiree',
        };
    }
}
