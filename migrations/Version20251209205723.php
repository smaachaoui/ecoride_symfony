<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209205723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, note INT NOT NULL, commentaire LONGTEXT NOT NULL, utilisateur_id_id INT DEFAULT NULL, covoiturage_id_id INT DEFAULT NULL, INDEX IDX_8F91ABF0B981C689 (utilisateur_id_id), INDEX IDX_8F91ABF07F316F4D (covoiturage_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0B981C689 FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF07F316F4D FOREIGN KEY (covoiturage_id_id) REFERENCES covoiturage (id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\')');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0B981C689');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF07F316F4D');
        $this->addSql('DROP TABLE avis');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL');
    }
}
