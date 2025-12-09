<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209202602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY `FK_28C79E89B981C689`');
        $this->addSql('DROP INDEX IDX_28C79E89B981C689 ON covoiturage');
        $this->addSql('ALTER TABLE covoiturage CHANGE utilisateur_id_id utilisateur INT NOT NULL');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E891D1C63B3 FOREIGN KEY (utilisateur) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_28C79E891D1C63B3 ON covoiturage (utilisateur)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\')');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY `FK_292FFF1DB981C689`');
        $this->addSql('DROP INDEX IDX_292FFF1DB981C689 ON vehicule');
        $this->addSql('ALTER TABLE vehicule ADD utilisateur INT NOT NULL, DROP utilisateur_id_id, CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\')');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D1D1C63B3 FOREIGN KEY (utilisateur) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_292FFF1D1D1C63B3 ON vehicule (utilisateur)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E891D1C63B3');
        $this->addSql('DROP INDEX IDX_28C79E891D1C63B3 ON covoiturage');
        $this->addSql('ALTER TABLE covoiturage CHANGE utilisateur utilisateur_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT `FK_28C79E89B981C689` FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_28C79E89B981C689 ON covoiturage (utilisateur_id_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D1D1C63B3');
        $this->addSql('DROP INDEX IDX_292FFF1D1D1C63B3 ON vehicule');
        $this->addSql('ALTER TABLE vehicule ADD utilisateur_id_id INT DEFAULT NULL, DROP utilisateur, CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT `FK_292FFF1DB981C689` FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_292FFF1DB981C689 ON vehicule (utilisateur_id_id)');
    }
}
