<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240208201322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email for person';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE person ADD email VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176E7927C74 ON person (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_34DCD176E7927C74');
        $this->addSql('ALTER TABLE person DROP email');
    }
}
