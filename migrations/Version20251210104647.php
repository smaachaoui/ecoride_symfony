<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210104647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule ADD couleur VARCHAR(50) DEFAULT NULL, CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule DROP couleur, CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL');
    }
}
