<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260731082144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerte (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, ville VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, secteur_id INT DEFAULT NULL, INDEX IDX_3AE753A9F7E4405 (secteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A9F7E4405 FOREIGN KEY (secteur_id) REFERENCES secteur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A9F7E4405');
        $this->addSql('DROP TABLE alerte');
    }
}
