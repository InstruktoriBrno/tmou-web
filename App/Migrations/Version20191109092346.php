<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191109092346 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team DROP COLUMN last_seen_discussion_at');
        $this->addSql('ALTER TABLE organizator DROP COLUMN last_seen_discussion_at');
        $this->addSql('CREATE TABLE thread_acknowledgement (
            id INT AUTO_INCREMENT NOT NULL, 
            thread_id INT NOT NULL,
            organizator_id INT NULL,
            team_id INT NULL,
            at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',   
            PRIMARY KEY(id),
            CHECK((organizator_id IS NULL AND team_id IS NOT NULL) OR (organizator_id IS NOT NULL AND team_id IS NULL)),
            UNIQUE(thread_id, organizator_id, team_id)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE thread_acknowledgement ADD CONSTRAINT thread_acknowledgement_thread_id FOREIGN KEY (thread_id) 
REFERENCES thread (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread_acknowledgement ADD CONSTRAINT thread_acknowledgement_organizator_id 
FOREIGN KEY (organizator_id) REFERENCES organizator (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thread_acknowledgement ADD CONSTRAINT thread_acknowledgement_team_id FOREIGN KEY (team_id) 
REFERENCES team (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team ADD COLUMN last_seen_discussion_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE organizator ADD COLUMN last_seen_discussion_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP TABLE thread_acknowledgement');
    }
}
