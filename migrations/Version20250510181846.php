<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510181846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE hexagon (
                latitude VARCHAR(255) NOT NULL, 
                longitude VARCHAR(255) NOT NULL, 
                color VARCHAR(7) NOT NULL, 
                owner VARCHAR(255) NOT NULL, 
                level INT NOT NULL, 
                PRIMARY KEY(latitude, longitude)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (
                id INT AUTO_INCREMENT NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                stravabucks INT NOT NULL, 
                UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE hexagon
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
