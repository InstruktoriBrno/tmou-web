<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190830183625 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE team (
            id INT AUTO_INCREMENT NOT NULL, 
            event_id INT NOT NULL, 
            number INT NOT NULL, 
            name VARCHAR(191) NOT NULL, 
            email VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\', 
            password_hash VARCHAR(255) NOT NULL, 
            password_reset_token VARCHAR(255) DEFAULT NULL, 
            password_reset_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            phrase TINYTEXT NOT NULL, phone TINYTEXT NOT NULL, 
            game_status VARCHAR(128) NOT NULL COMMENT \'(DC2Type:game_status)\', 
            payment_status VARCHAR(128) NOT NULL COMMENT \'(DC2Type:payment_status)\', 
            payment_paired_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            team_review_id INT DEFAULT NULL, registered_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            last_updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            last_logged_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            INDEX fk_event_id (event_id), UNIQUE INDEX unique_number_in_event_idx (event_id, number), 
            UNIQUE INDEX unique_email_in_event_idx (event_id, email), 
            UNIQUE INDEX unique_name_in_event_idx (event_id, name), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_member (
            id INT AUTO_INCREMENT NOT NULL, 
            team_id INT DEFAULT NULL, 
            number INT NOT NULL, 
            full_name TINYTEXT NOT NULL, 
            email TINYTEXT DEFAULT NULL, 
            age INT DEFAULT NULL, 
            add_to_newsletter TINYINT(1) NOT NULL, 
            INDEX IDX_6FFBDA1296CD8AE (team_id), 
            UNIQUE INDEX unique_number_in_team_idx (team_id, number), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_member ADD CONSTRAINT fk_team_id FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event 
            ADD payment_pairing_code_prefix VARCHAR(255) DEFAULT NULL, 
            ADD payment_pairing_code_suffix_length INT DEFAULT NULL, 
            ADD registration_deadline DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD change_deadline DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            CHANGE event_start event_start DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            CHANGE event_end event_end DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE team_review (
            id INT AUTO_INCREMENT NOT NULL, 
            positives LONGTEXT NOT NULL, 
            negatives LONGTEXT NOT NULL, 
            others LONGTEXT NOT NULL, 
            link LONGTEXT NOT NULL, PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT fk_team_review_id FOREIGN KEY (team_review_id) REFERENCES team_review (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE team_member');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_review');
        $this->addSql('ALTER TABLE event DROP payment_pairing_code_prefix, DROP payment_pairing_code_suffix_length, DROP registration_deadline');
    }
}
