<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260803100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize the legacy Bac study level to the NiveauEtude backing value';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE offre SET niveau_etude = 'BAC' WHERE niveau_etude = 'Bac'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE offre SET niveau_etude = 'Bac' WHERE niveau_etude = 'BAC'");
    }
}
