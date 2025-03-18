<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318083736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE credit_card ADD encrypted_number VARCHAR(255) NOT NULL, ADD encrypted_cvv VARCHAR(255) NOT NULL, ADD first4 VARCHAR(4) NOT NULL, ADD last4 VARCHAR(4) NOT NULL, ADD card_type VARCHAR(50) DEFAULT NULL, DROP number, DROP cvv, CHANGE holder_name encrypted_holder_name VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE credit_card ADD number VARCHAR(19) NOT NULL, ADD cvv VARCHAR(3) NOT NULL, DROP encrypted_number, DROP encrypted_cvv, DROP first4, DROP last4, DROP card_type, CHANGE encrypted_holder_name holder_name VARCHAR(500) NOT NULL');
    }
}
