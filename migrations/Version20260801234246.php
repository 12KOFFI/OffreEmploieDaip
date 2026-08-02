<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260801234246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE journal_activite CHANGE action action VARCHAR(255) DEFAULT NULL, CHANGE cible_type cible_type VARCHAR(255) DEFAULT NULL, CHANGE utilisateur_email utilisateur_email VARCHAR(180) DEFAULT NULL, CHANGE adresse_ip adresse_ip VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE offre ADD nombre_postes INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE journal_activite CHANGE action action VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE cible_type cible_type VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE utilisateur_email utilisateur_email VARCHAR(180) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE adresse_ip adresse_ip VARCHAR(45) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE offre DROP nombre_postes');
    }
}
