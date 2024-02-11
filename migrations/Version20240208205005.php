<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240208205005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add api_user table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE api_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE api_user (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, api_key VARCHAR(32), PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AC64A0BAE7927C74 ON api_user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_user');
        $this->addSql('DROP SEQUENCE api_user_id_seq CASCADE');
    }
}
