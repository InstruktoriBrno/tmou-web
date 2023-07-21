<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230721101010 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // Add table for cached qualification results
        $this->addSql(<<<EOD
CREATE TABLE cached_qualification_results (
    event_id INT NOT NULL,
    team_id INT NOT NULL,
    position INT NOT NULL,
    qualified TINYINT(1) NOT NULL,
    max_reached_level INT NULL,
    total_answer_count INT NULL,
    latest_answer_at DATETIME NULL,
    latest_answer_id INT NULL,
    populated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX unique_team_in_event_qualification_results (event_id, team_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE cached_qualification_results ADD CONSTRAINT fk_results_event_id FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE;
ALTER TABLE cached_qualification_results ADD CONSTRAINT fk_results_team_id FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE;
EOD);
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE cached_qualification_results;');
    }
}
