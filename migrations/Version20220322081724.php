<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220322081724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, duration_type_id INT NOT NULL, identification_type_id INT NOT NULL, code VARCHAR(15) NOT NULL, subject_es VARCHAR(1024) NOT NULL, subject_eu VARCHAR(1024) NOT NULL, amount_with_vat NUMERIC(10, 2) NOT NULL, duration NUMERIC(10, 2) NOT NULL, id_number VARCHAR(15) NOT NULL, enterprise VARCHAR(255) NOT NULL, award_date DATE NOT NULL, INDEX IDX_E98F2859C54C8C93 (type_id), INDEX IDX_E98F285980CA3F3B (duration_type_id), INDEX IDX_E98F2859F54A83F (identification_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE duration_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE identification_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859C54C8C93 FOREIGN KEY (type_id) REFERENCES contract_type (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F285980CA3F3B FOREIGN KEY (duration_type_id) REFERENCES duration_type (id)');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859F54A83F FOREIGN KEY (identification_type_id) REFERENCES identification_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859C54C8C93');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F285980CA3F3B');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859F54A83F');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE contract_type');
        $this->addSql('DROP TABLE duration_type');
        $this->addSql('DROP TABLE identification_type');
    }
}
