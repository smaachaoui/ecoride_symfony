<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212135855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation CHANGE statut statut ENUM(\'en_attente\', \'accepte\', \'refuse\') DEFAULT \'en_attente\'');
        $this->addSql('ALTER TABLE preferences CHANGE utilisateur_id utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation CHANGE statut statut ENUM(\'en_attente\', \'accepte\', \'refuse\') DEFAULT \'en_attente\'');
        $this->addSql('ALTER TABLE preferences CHANGE utilisateur_id utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL');
    }
}
