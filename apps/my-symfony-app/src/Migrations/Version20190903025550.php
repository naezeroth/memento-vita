<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190903025550 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE habit ADD user_belongs_to_id INT NOT NULL');
        $this->addSql('ALTER TABLE habit ADD CONSTRAINT FK_44FE2172DD46FAA5 FOREIGN KEY (user_belongs_to_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_44FE2172DD46FAA5 ON habit (user_belongs_to_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE habit DROP FOREIGN KEY FK_44FE2172DD46FAA5');
        $this->addSql('DROP INDEX IDX_44FE2172DD46FAA5 ON habit');
        $this->addSql('ALTER TABLE habit DROP user_belongs_to_id');
    }
}
