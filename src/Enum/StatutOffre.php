<?php

namespace App\Enum;

enum StatutOffre: string
{
    case BROUILLON = 'brouillon';
    case PUBLIEE = 'publiee';
    case RETIREE = 'retiree';
    case EXPIREE = 'expiree';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::PUBLIEE   => 'Publiée',
            self::RETIREE   => 'Retirée',
            self::EXPIREE   => 'Expirée',
        };
    }
}
