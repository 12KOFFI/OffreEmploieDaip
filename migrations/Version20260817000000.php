<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les index manquants sur les colonnes les plus filtrées (audit C2 / P4).
 */
final class Version20260817000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing indexes on user.reset_token, offre.statut/date_publication/date_expiration, offre_metier.ville';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_user_reset_token ON `user` (reset_token)');
        $this->addSql('CREATE INDEX idx_offre_statut ON offre (statut)');
        $this->addSql('CREATE INDEX idx_offre_date_publication ON offre (date_publication)');
        $this->addSql('CREATE INDEX idx_offre_date_expiration ON offre (date_expiration)');
        $this->addSql('CREATE INDEX idx_offre_metier_ville ON offre_metier (ville)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_user_reset_token ON `user`');
        $this->addSql('DROP INDEX idx_offre_statut ON offre');
        $this->addSql('DROP INDEX idx_offre_date_publication ON offre');
        $this->addSql('DROP INDEX idx_offre_date_expiration ON offre');
        $this->addSql('DROP INDEX idx_offre_metier_ville ON offre_metier');
    }
}
