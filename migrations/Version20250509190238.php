<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509190238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE hexagon (
                latitude VARCHAR(255) NOT NULL,
                longitude VARCHAR(255) NOT NULL,
                color VARCHAR(7) NOT NULL,
                owner VARCHAR(255) DEFAULT NULL,
                level INT NOT NULL,
                PRIMARY KEY (latitude, longitude)
            )
        SQL);
    }


    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE hexagon');
    }
}
