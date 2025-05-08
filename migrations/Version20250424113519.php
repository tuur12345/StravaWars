<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424113519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE hexagon MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON hexagon
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hexagon DROP id, DROP hexagon_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE hexagon ADD PRIMARY KEY (latitude, longitude)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE hexagon ADD id INT AUTO_INCREMENT NOT NULL, ADD hexagon_id VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)
        SQL);
    }
}
