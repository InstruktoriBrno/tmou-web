<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190909180847 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE team_sso_session (
            id INT AUTO_INCREMENT NOT NULL, 
            team_id INT NOT NULL, 
            token VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\', 
            valid TINYINT(1) NOT NULL, 
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            INDEX team_sso_session_team_id (team_id), 
            PRIMARY KEY(id),
            UNIQUE(token)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_sso_session ADD CONSTRAINT team_sso_session_team_id FOREIGN KEY (team_id) REFERENCES team (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE team_sso_session');
    }
}
