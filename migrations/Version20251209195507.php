<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209195507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY `avis_ibfk_1`');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY `avis_ibfk_2`');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY `covoiturage_ibfk_1`');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY `covoiturage_ibfk_2`');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY `participation_ibfk_1`');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY `participation_ibfk_2`');
        $this->addSql('ALTER TABLE preferences DROP FOREIGN KEY `preferences_ibfk_1`');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY `vehicule_ibfk_1`');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE covoiturage');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE preferences');
        $this->addSql('DROP TABLE vehicule');
        $this->addSql('DROP INDEX email ON utilisateur');
        $this->addSql('DROP INDEX pseudo ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur CHANGE credits credits INT DEFAULT NULL, CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\'), CHANGE is_profile_configured is_profile_configured TINYINT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, covoiturage_id INT DEFAULT NULL, note INT DEFAULT NULL, commentaire TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, date_avis DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX utilisateur_id (utilisateur_id), INDEX covoiturage_id (covoiturage_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE covoiturage (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, ville_depart VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, ville_arrivee VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, date_depart DATETIME NOT NULL, prix NUMERIC(10, 2) NOT NULL, places_restantes INT NOT NULL, ecologique TINYINT DEFAULT 0, vehicule_id INT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX utilisateur_id (utilisateur_id), INDEX vehicule_id (vehicule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, covoiturage_id INT DEFAULT NULL, confirme TINYINT DEFAULT 0, credits_utilises INT NOT NULL, INDEX covoiturage_id (covoiturage_id), UNIQUE INDEX utilisateur_id (utilisateur_id, covoiturage_id), INDEX IDX_AB55E24FFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE preferences (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, accepte_fumeurs TINYINT DEFAULT 1, accepte_animaux TINYINT DEFAULT 1, preferences_chauffeur TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, INDEX utilisateur_id (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, modele VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, marque VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, couleur VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, immatriculation VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, date_premiere_immatriculation DATE NOT NULL, places_disponibles INT NOT NULL, UNIQUE INDEX immatriculation (immatriculation), INDEX utilisateur_id (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT `covoiturage_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT `covoiturage_ibfk_2` FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT `participation_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT `participation_ibfk_2` FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preferences ADD CONSTRAINT `preferences_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT `vehicule_ibfk_1` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur CHANGE credits credits INT DEFAULT 20, CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') NOT NULL, CHANGE is_profile_configured is_profile_configured TINYINT DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX email ON utilisateur (email)');
        $this->addSql('CREATE UNIQUE INDEX pseudo ON utilisateur (pseudo)');
    }
}
