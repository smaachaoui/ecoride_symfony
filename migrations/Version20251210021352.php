<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210021352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY `FK_8F91ABF062671590`');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY `FK_8F91ABF0FB88E14F`');
        $this->addSql('DROP INDEX IDX_8F91ABF062671590 ON avis');
        $this->addSql('DROP INDEX IDX_8F91ABF0FB88E14F ON avis');
        $this->addSql('ALTER TABLE avis ADD statut VARCHAR(20) NOT NULL, ADD created_at DATETIME NOT NULL, ADD utilisateur_id_id INT NOT NULL, ADD covoiturage_id_id INT NOT NULL, DROP utilisateur_id, DROP covoiturage_id, CHANGE commentaire commentaire LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0B981C689 FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF07F316F4D FOREIGN KEY (covoiturage_id_id) REFERENCES covoiturage (id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0B981C689 ON avis (utilisateur_id_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF07F316F4D ON avis (covoiturage_id_id)');
        $this->addSql('ALTER TABLE utilisateur ADD roles_system JSON NOT NULL, ADD is_suspended TINYINT DEFAULT NULL, ADD photo VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B386CC499D ON utilisateur (pseudo)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0B981C689');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF07F316F4D');
        $this->addSql('DROP INDEX IDX_8F91ABF0B981C689 ON avis');
        $this->addSql('DROP INDEX IDX_8F91ABF07F316F4D ON avis');
        $this->addSql('ALTER TABLE avis ADD utilisateur_id INT NOT NULL, ADD covoiturage_id INT NOT NULL, DROP statut, DROP created_at, DROP utilisateur_id_id, DROP covoiturage_id_id, CHANGE commentaire commentaire LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT `FK_8F91ABF062671590` FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT `FK_8F91ABF0FB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8F91ABF062671590 ON avis (covoiturage_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0FB88E14F ON avis (utilisateur_id)');
        $this->addSql('DROP INDEX UNIQ_1D1C63B386CC499D ON utilisateur');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP roles_system, DROP is_suspended, DROP photo, CHANGE role role ENUM(\'CHAUFFEUR\', \'PASSAGER\', \'CHAUFFEUR_PASSAGER\') DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule CHANGE energie energie ENUM(\'Essence\', \'Diesel\', \'Electrique\', \'Hybride\') DEFAULT NULL');
    }
}
