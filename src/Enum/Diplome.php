<?php

namespace App\Enum;

enum Diplome: string
{
    // Enseignement Général (1er et 2nd cycle)
    case CEPE = 'CEPE (Certificat d’Études Primaires Élémentaires)';
    case BEPC = 'BEPC (Brevet d’Études du Premier Cycle)';
    case BAC = 'BAC (Baccalauréat)';

    // Formation professionnelle / technique
    case AUCUN = 'Aucun diplôme';
    case CQP = 'CQP (Certificat de Qualification Professionnelle)';
    case CAP = 'CAP (Certificat d’Aptitude Professionnelle)';
    case BEP = 'BEP (Brevet d’Études Professionnelles)';
    case BT = 'BT (Brevet de Technicien)';
    case BTS = 'BTS (Brevet de Technicien Supérieur)';

    // Universitaire
    case DUT = 'DUT';
    case LICENCE = 'Licence';
    case LICENCE_PRO = 'Licence Professionnelle';
    case MASTER = 'Master';
    case MASTER_PRO = 'Master Professionnel';
    case INGENIEUR = 'Ingénieur';
    case DOCTORAT = 'Doctorat';

    // Autres
    case DIPLOME_ETAT = 'Diplôme d’État';
    case EQUIVALENT = 'Diplôme professionnel équivalent';
    case AUTRE = 'AUTRE';

    public function label(): string
    {
        return $this->value;
    }
}
