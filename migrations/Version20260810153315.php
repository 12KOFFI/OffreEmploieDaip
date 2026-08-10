<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260810153315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE journal_activite');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA60A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE offre_metier ADD CONSTRAINT FK_362A6DA74CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE offre_metier ADD CONSTRAINT FK_362A6DA7ED16FA20 FOREIGN KEY (metier_id) REFERENCES metier (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE journal_activite (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, cible_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, cible_id INT DEFAULT NULL, utilisateur_email VARCHAR(180) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, adresse_ip VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, date DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA60A76ED395');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FA4AEAFEA');
        $this->addSql('ALTER TABLE offre_metier DROP FOREIGN KEY FK_362A6DA74CC8505A');
        $this->addSql('ALTER TABLE offre_metier DROP FOREIGN KEY FK_362A6DA7ED16FA20');
    }
}
