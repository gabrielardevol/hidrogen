<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512155622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE user_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER id TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER id DROP DEFAULT
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE user_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('user_id_seq', (SELECT MAX(id) FROM "user"))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER id SET DEFAULT nextval('user_id_seq')
        SQL);
    }
}
