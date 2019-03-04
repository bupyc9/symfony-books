<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190304202131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE books DROP CONSTRAINT FK_4A1B2A92F675F31B');
        $this->addSql('ALTER TABLE books ADD CONSTRAINT FK_4A1B2A92F675F31B FOREIGN KEY (author_id) REFERENCES authors (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE books DROP CONSTRAINT fk_4a1b2a92f675f31b');
        $this->addSql('ALTER TABLE books ADD CONSTRAINT fk_4a1b2a92f675f31b FOREIGN KEY (author_id) REFERENCES authors (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
