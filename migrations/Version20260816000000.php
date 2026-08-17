<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration adding contact_responsable and autre_contact to entreprise table.
 */
final class Version20260816000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add contact_responsable and autre_contact columns to entreprise table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entreprise ADD contact_responsable VARCHAR(50) DEFAULT NULL, ADD autre_contact VARCHAR(50) DEFAULT NULL, CHANGE contact contact VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entreprise DROP contact_responsable, DROP autre_contact, CHANGE contact contact VARCHAR(255) DEFAULT NULL');
    }
}
