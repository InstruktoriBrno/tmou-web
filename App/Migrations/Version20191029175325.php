<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191029175325 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE thread (
            id INT AUTO_INCREMENT NOT NULL, 
            event_id INT NULL,
            title VARCHAR(191) NOT NULL,
            organizator_id INT NULL,
            team_id INT NULL,
            locked TINYINT(1) NOT NULL DEFAULT FALSE,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',  
            PRIMARY KEY(id),
            CHECK((organizator_id IS NULL AND team_id IS NOT NULL) OR (organizator_id IS NOT NULL AND team_id IS NULL))
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT thread_event_id FOREIGN KEY (event_id) REFERENCES event (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT thread_organizator_id FOREIGN KEY (organizator_id) REFERENCES organizator (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT thread_team_id FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE CASCADE ON DELETE CASCADE');

        $this->addSql('CREATE TABLE post (
            id INT AUTO_INCREMENT NOT NULL, 
            thread_id INT NOT NULL,
            content TEXT NOT NULL,
            organizator_id INT NULL,
            team_id INT NULL,
            hidden TINYINT(1) NOT NULL DEFAULT FALSE,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',   
            PRIMARY KEY(id),
            CHECK((organizator_id IS NULL AND team_id IS NOT NULL) OR (organizator_id IS NOT NULL AND team_id IS NULL))
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT post_thread_id FOREIGN KEY (thread_id) REFERENCES thread (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT post_organizator_id FOREIGN KEY (organizator_id) REFERENCES organizator (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT post_team_id FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE CASCADE ON DELETE CASCADE');

        $this->addSql('ALTER TABLE team ADD COLUMN last_seen_discussion_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE organizator ADD COLUMN last_seen_discussion_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team DROP COLUMN last_seen_discussion_at');
        $this->addSql('ALTER TABLE organizator DROP COLUMN last_seen_discussion_at');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE thread');
    }
}
