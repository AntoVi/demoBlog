<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211214150435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalogue ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE catalogue ADD CONSTRAINT FK_59A699F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_59A699F5A76ED395 ON catalogue (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalogue DROP FOREIGN KEY FK_59A699F5A76ED395');
        $this->addSql('DROP INDEX IDX_59A699F5A76ED395 ON catalogue');
        $this->addSql('ALTER TABLE catalogue DROP user_id');
    }
}
