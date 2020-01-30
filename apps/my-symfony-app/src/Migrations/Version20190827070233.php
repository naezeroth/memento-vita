<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190827070233 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE goal (id INT AUTO_INCREMENT NOT NULL, user_belongs_to_id INT NOT NULL, purpose_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATE NOT NULL, milestone LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_FCDCEB2EDD46FAA5 (user_belongs_to_id), INDEX IDX_FCDCEB2E7FC21131 (purpose_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE habit (id INT AUTO_INCREMENT NOT NULL, goal_id INT NOT NULL, description VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, recurrence VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, done LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', INDEX IDX_44FE2172667D1AFE (goal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purpose (id INT AUTO_INCREMENT NOT NULL, user_belongs_to_id INT NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_B887B3EBDD46FAA5 (user_belongs_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2EDD46FAA5 FOREIGN KEY (user_belongs_to_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E7FC21131 FOREIGN KEY (purpose_id) REFERENCES purpose (id)');
        $this->addSql('ALTER TABLE habit ADD CONSTRAINT FK_44FE2172667D1AFE FOREIGN KEY (goal_id) REFERENCES goal (id)');
        $this->addSql('ALTER TABLE purpose ADD CONSTRAINT FK_B887B3EBDD46FAA5 FOREIGN KEY (user_belongs_to_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE habit DROP FOREIGN KEY FK_44FE2172667D1AFE');
        $this->addSql('ALTER TABLE goal DROP FOREIGN KEY FK_FCDCEB2E7FC21131');
        $this->addSql('DROP TABLE goal');
        $this->addSql('DROP TABLE habit');
        $this->addSql('DROP TABLE purpose');
    }
}
