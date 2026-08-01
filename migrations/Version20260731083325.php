<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260731083325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE journal_activite (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(255) DEFAULT NULL, cible_type VARCHAR(255) DEFAULT NULL, cible_id INT DEFAULT NULL, utilisateur_email VARCHAR(180) DEFAULT NULL, adresse_ip VARCHAR(45) DEFAULT NULL, date DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE alerte ADD CONSTRAINT FK_3AE753A9F7E4405 FOREIGN KEY (secteur_id) REFERENCES secteur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE journal_activite');
        $this->addSql('ALTER TABLE alerte DROP FOREIGN KEY FK_3AE753A9F7E4405');
    }
}
