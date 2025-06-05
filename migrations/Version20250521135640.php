<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521135640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE chat ADD publishing_user_id VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat ADD interested_user_id VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat DROP publishing_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat DROP interested_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat ADD CONSTRAINT FK_659DF2AAE619DA5E FOREIGN KEY (publishing_user_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat ADD CONSTRAINT FK_659DF2AAB54B2A53 FOREIGN KEY (interested_user_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_659DF2AAE619DA5E ON chat (publishing_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_659DF2AAB54B2A53 ON chat (interested_user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat DROP CONSTRAINT FK_659DF2AAE619DA5E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat DROP CONSTRAINT FK_659DF2AAB54B2A53
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_659DF2AAE619DA5E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_659DF2AAB54B2A53
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat ADD publishing_user VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat ADD interested_user VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat DROP publishing_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat DROP interested_user_id
        SQL);
    }
}
