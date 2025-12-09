<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209210340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY `FK_8F91ABF07F316F4D`');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY `FK_8F91ABF0B981C689`');
        $this->addSql('DROP INDEX IDX_8F91ABF07F316F4D ON avis');
        $this->addSql('DROP INDEX IDX_8F91ABF0B981C689 ON avis');
        $this->addSql('ALTER TABLE avis ADD utilisateur_id INT NOT NULL, ADD covoiturage_id INT NOT NULL, DROP utilisateur_id_id, DROP covoiturage_id_id');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF062671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0FB88E14F ON avis (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF062671590 ON avis (covoiturage_id)');
        $this->addSql('ALTER TABLE preferences DROP FOREIGN KEY `FK_E931A6F5B981C689`');
        $this->addSql('DROP INDEX UNIQ_E931A6F5B981C689 ON preferences');
        $this->addSql('ALTER TABLE preferences ADD utilisateur_id INT NOT NULL, DROP utilisateur_id_id');
        $this->addSql('ALTER TABLE preferences ADD CONSTRAINT FK_E931A6F5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E931A6F5FB88E14F ON preferences (utilisateur_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\')');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0FB88E14F');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF062671590');
        $this->addSql('DROP INDEX IDX_8F91ABF0FB88E14F ON avis');
        $this->addSql('DROP INDEX IDX_8F91ABF062671590 ON avis');
        $this->addSql('ALTER TABLE avis ADD utilisateur_id_id INT DEFAULT NULL, ADD covoiturage_id_id INT DEFAULT NULL, DROP utilisateur_id, DROP covoiturage_id');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT `FK_8F91ABF07F316F4D` FOREIGN KEY (covoiturage_id_id) REFERENCES covoiturage (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT `FK_8F91ABF0B981C689` FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8F91ABF07F316F4D ON avis (covoiturage_id_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0B981C689 ON avis (utilisateur_id_id)');
        $this->addSql('ALTER TABLE preferences DROP FOREIGN KEY FK_E931A6F5FB88E14F');
        $this->addSql('DROP INDEX UNIQ_E931A6F5FB88E14F ON preferences');
        $this->addSql('ALTER TABLE preferences ADD utilisateur_id_id INT DEFAULT NULL, DROP utilisateur_id');
        $this->addSql('ALTER TABLE preferences ADD CONSTRAINT `FK_E931A6F5B981C689` FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E931A6F5B981C689 ON preferences (utilisateur_id_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL');
    }
}
