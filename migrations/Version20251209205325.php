<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209205325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY `FK_AB55E24F7F316F4D`');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY `FK_AB55E24FB981C689`');
        $this->addSql('DROP INDEX IDX_AB55E24F7F316F4D ON participation');
        $this->addSql('DROP INDEX IDX_AB55E24FB981C689 ON participation');
        $this->addSql('ALTER TABLE participation ADD utilisateur_id INT NOT NULL, ADD covoiturage_id INT NOT NULL, DROP utilisateur_id_id, DROP covoiturage_id_id');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F62671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id)');
        $this->addSql('CREATE INDEX IDX_AB55E24FFB88E14F ON participation (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_AB55E24F62671590 ON participation (covoiturage_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\')');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FFB88E14F');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F62671590');
        $this->addSql('DROP INDEX IDX_AB55E24FFB88E14F ON participation');
        $this->addSql('DROP INDEX IDX_AB55E24F62671590 ON participation');
        $this->addSql('ALTER TABLE participation ADD utilisateur_id_id INT DEFAULT NULL, ADD covoiturage_id_id INT DEFAULT NULL, DROP utilisateur_id, DROP covoiturage_id');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT `FK_AB55E24F7F316F4D` FOREIGN KEY (covoiturage_id_id) REFERENCES covoiturage (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT `FK_AB55E24FB981C689` FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AB55E24F7F316F4D ON participation (covoiturage_id_id)');
        $this->addSql('CREATE INDEX IDX_AB55E24FB981C689 ON participation (utilisateur_id_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL');
    }
}
