<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260730121308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE offre SET niveau_etude = 'Bac +2 (BTS / DUT)' WHERE niveau_etude = 'BTS'");
        $this->addSql('ALTER TABLE competence CHANGE nom nom VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE entreprise CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE siret siret VARCHAR(50) DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE logo logo VARCHAR(255) DEFAULT NULL, CHANGE site_web site_web VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA60A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE offre DROP motif_rejet, CHANGE titre titre VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE type_contrat type_contrat VARCHAR(20) NOT NULL, CHANGE ville ville VARCHAR(100) NOT NULL, CHANGE niveau_etude niveau_etude VARCHAR(50) DEFAULT NULL, CHANGE statut statut VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F9F7E4405 FOREIGN KEY (secteur_id) REFERENCES secteur (id)');
        $this->addSql('ALTER TABLE offre_competence ADD CONSTRAINT FK_B98A0F5A4CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offre_competence ADD CONSTRAINT FK_B98A0F5A15761DAB FOREIGN KEY (competence_id) REFERENCES competence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE secteur CHANGE nom nom VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE body body LONGTEXT NOT NULL, CHANGE headers headers LONGTEXT NOT NULL, CHANGE queue_name queue_name VARCHAR(190) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE offre SET niveau_etude = 'Bac +2 (BTS / DUT)' WHERE niveau_etude = 'BTS'");
        $this->addSql('ALTER TABLE competence CHANGE nom nom VARCHAR(100) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA60A76ED395');
        $this->addSql('ALTER TABLE entreprise CHANGE nom nom VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE siret siret VARCHAR(50) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE description description LONGTEXT DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE logo logo VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE site_web site_web VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE messenger_messages CHANGE body body LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE headers headers LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE queue_name queue_name VARCHAR(190) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FA4AEAFEA');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F9F7E4405');
        $this->addSql('ALTER TABLE offre ADD motif_rejet LONGTEXT DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE titre titre VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE description description LONGTEXT NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE type_contrat type_contrat VARCHAR(20) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE ville ville VARCHAR(100) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE niveau_etude niveau_etude VARCHAR(50) DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE statut statut VARCHAR(20) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE offre_competence DROP FOREIGN KEY FK_B98A0F5A4CC8505A');
        $this->addSql('ALTER TABLE offre_competence DROP FOREIGN KEY FK_B98A0F5A15761DAB');
        $this->addSql('ALTER TABLE secteur CHANGE nom nom VARCHAR(100) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE `user` CHANGE email email VARCHAR(180) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
    }
}
