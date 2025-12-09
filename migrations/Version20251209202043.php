<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209202043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE covoiturage (id INT AUTO_INCREMENT NOT NULL, ville_depart VARCHAR(255) NOT NULL, ville_arrivee VARCHAR(255) NOT NULL, date_depart DATETIME NOT NULL, prix NUMERIC(10, 2) NOT NULL, places_restantes INT NOT NULL, ecologique TINYINT NOT NULL, utilisateur_id_id INT NOT NULL, vehicule_id_id INT NOT NULL, INDEX IDX_28C79E89B981C689 (utilisateur_id_id), INDEX IDX_28C79E894F9D6605 (vehicule_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, modele VARCHAR(255) NOT NULL, marque VARCHAR(255) NOT NULL, immatriculation VARCHAR(255) NOT NULL, energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\'), date_premiere_immatriculation DATE NOT NULL, places_disponibles INT NOT NULL, utilisateur_id_id INT DEFAULT NULL, INDEX IDX_292FFF1DB981C689 (utilisateur_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E89B981C689 FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E894F9D6605 FOREIGN KEY (vehicule_id_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DB981C689 FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E89B981C689');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E894F9D6605');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DB981C689');
        $this->addSql('DROP TABLE covoiturage');
        $this->addSql('DROP TABLE vehicule');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') DEFAULT NULL');
    }
}
