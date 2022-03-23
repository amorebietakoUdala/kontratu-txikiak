<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220322084653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE duration_type CHANGE name name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE identification_type CHANGE name name VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract CHANGE code code VARCHAR(15) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE subject_es subject_es VARCHAR(1024) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE subject_eu subject_eu VARCHAR(1024) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE id_number id_number VARCHAR(15) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE enterprise enterprise VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE contract_type CHANGE name name VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE duration_type CHANGE name name VARCHAR(20) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE identification_type CHANGE name name VARCHAR(20) NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
