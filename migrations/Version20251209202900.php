<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209202900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY `FK_28C79E891D1C63B3`');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY `FK_28C79E894F9D6605`');
        $this->addSql('DROP INDEX IDX_28C79E894F9D6605 ON covoiturage');
        $this->addSql('DROP INDEX IDX_28C79E891D1C63B3 ON covoiturage');
        $this->addSql('ALTER TABLE covoiturage ADD utilisateur_id INT NOT NULL, ADD vehicule_id INT NOT NULL, DROP utilisateur, DROP vehicule_id_id');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E89FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E894A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('CREATE INDEX IDX_28C79E89FB88E14F ON covoiturage (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_28C79E894A4A3511 ON covoiturage (vehicule_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\')');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY `FK_292FFF1D1D1C63B3`');
        $this->addSql('DROP INDEX IDX_292FFF1D1D1C63B3 ON vehicule');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\'), CHANGE utilisateur utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_292FFF1DFB88E14F ON vehicule (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E89FB88E14F');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E894A4A3511');
        $this->addSql('DROP INDEX IDX_28C79E89FB88E14F ON covoiturage');
        $this->addSql('DROP INDEX IDX_28C79E894A4A3511 ON covoiturage');
        $this->addSql('ALTER TABLE covoiturage ADD utilisateur INT NOT NULL, ADD vehicule_id_id INT NOT NULL, DROP utilisateur_id, DROP vehicule_id');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT `FK_28C79E891D1C63B3` FOREIGN KEY (utilisateur) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT `FK_28C79E894F9D6605` FOREIGN KEY (vehicule_id_id) REFERENCES vehicule (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_28C79E894F9D6605 ON covoiturage (vehicule_id_id)');
        $this->addSql('CREATE INDEX IDX_28C79E891D1C63B3 ON covoiturage (utilisateur)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DFB88E14F');
        $this->addSql('DROP INDEX IDX_292FFF1DFB88E14F ON vehicule');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL, CHANGE utilisateur_id utilisateur INT NOT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT `FK_292FFF1D1D1C63B3` FOREIGN KEY (utilisateur) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_292FFF1D1D1C63B3 ON vehicule (utilisateur)');
    }
}
