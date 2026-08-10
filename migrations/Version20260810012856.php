<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260810012856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE metier (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_51A00D8C6C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offre_metier (id INT AUTO_INCREMENT NOT NULL, type_contrat VARCHAR(20) DEFAULT NULL, ville VARCHAR(100) NOT NULL, nombre_postes INT NOT NULL, nb_annees_experience INT DEFAULT NULL, niveau_etude VARCHAR(50) DEFAULT NULL, salaire_min INT DEFAULT NULL, salaire_max INT DEFAULT NULL, prerequis LONGTEXT DEFAULT NULL, offre_id INT NOT NULL, metier_id INT NOT NULL, INDEX IDX_362A6DA74CC8505A (offre_id), INDEX IDX_362A6DA7ED16FA20 (metier_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE offre_metier ADD CONSTRAINT FK_362A6DA74CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE offre_metier ADD CONSTRAINT FK_362A6DA7ED16FA20 FOREIGN KEY (metier_id) REFERENCES metier (id)');
        $this->addSql('DROP TABLE competence');
        $this->addSql('DROP TABLE offre_competence');
        $this->addSql('DROP TABLE secteur');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA60A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('DROP INDEX IDX_AF86866F9F7E4405 ON offre');
        $this->addSql('ALTER TABLE offre ADD date_debut DATETIME DEFAULT NULL, DROP type_contrat, DROP ville, DROP salaire_min, DROP salaire_max, DROP nb_annees_experience, DROP niveau_etude, DROP secteur_id, DROP nombre_postes, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE date_publication date_publication DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE competence (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, UNIQUE INDEX UNIQ_94D4687F6C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE offre_competence (offre_id INT NOT NULL, competence_id INT NOT NULL, INDEX IDX_B98A0F5A15761DAB (competence_id), INDEX IDX_B98A0F5A4CC8505A (offre_id), PRIMARY KEY (offre_id, competence_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE secteur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, UNIQUE INDEX UNIQ_8045251F6C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('ALTER TABLE offre_metier DROP FOREIGN KEY FK_362A6DA74CC8505A');
        $this->addSql('ALTER TABLE offre_metier DROP FOREIGN KEY FK_362A6DA7ED16FA20');
        $this->addSql('DROP TABLE metier');
        $this->addSql('DROP TABLE offre_metier');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA60A76ED395');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FA4AEAFEA');
        $this->addSql('ALTER TABLE offre ADD type_contrat VARCHAR(20) NOT NULL, ADD ville VARCHAR(100) NOT NULL, ADD salaire_min INT DEFAULT NULL, ADD salaire_max INT DEFAULT NULL, ADD nb_annees_experience INT NOT NULL, ADD niveau_etude VARCHAR(50) DEFAULT NULL, ADD secteur_id INT DEFAULT NULL, ADD nombre_postes INT NOT NULL, DROP date_debut, CHANGE description description LONGTEXT NOT NULL, CHANGE date_publication date_publication DATETIME NOT NULL');
        $this->addSql('CREATE INDEX IDX_AF86866F9F7E4405 ON offre (secteur_id)');
    }
}
