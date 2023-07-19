<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230615101010 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // Add extra colums for the qualification system
        $this->addSql('ALTER TABLE event ADD COLUMN qualification_max_attempts INT NULL');
        $this->addSql('ALTER TABLE event ADD COLUMN qualification_show_attempts_count TINYINT(1) NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE event ADD COLUMN qualification_wrong_attempt_penalisation INT NULL');
        $this->addSql('ALTER TABLE event ADD COLUMN qualification_show_next_attempt_time TINYINT(1) NOT NULL DEFAULT FALSE');

        // Add levels table
        $this->addSql(<<<EOD
CREATE TABLE level (
    id INT AUTO_INCREMENT NOT NULL,
    event_id INT NOT NULL,
    level_number INT NOT NULL,
    link TEXT NULL,
    backup_link TEXT NULL,
    needed_correct_answers INT NULL,
    INDEX idx_event_id (event_id),
    UNIQUE INDEX unique_level_number_in_event (event_id, level_number),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE level ADD CONSTRAINT fk_level_event_id FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE;
EOD);
        // Add puzzles table
        $this->addSql(<<<EOD
CREATE TABLE puzzle (
    id INT AUTO_INCREMENT NOT NULL,
    level_id INT NOT NULL,
    name TEXT NOT NULL,
    INDEX idx_level_id (level_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE puzzle ADD CONSTRAINT fk_puzzle_level_id FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE
EOD);
        // Add passwords table
        $this->addSql(<<<EOD
CREATE TABLE password (
    id INT AUTO_INCREMENT NOT NULL,
    puzzle_id INT NOT NULL,
    code TEXT NOT NULL,
    INDEX idx_puzzle_id (puzzle_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE password ADD CONSTRAINT fk_password_puzzle_id FOREIGN KEY (puzzle_id) REFERENCES puzzle (id) ON DELETE CASCADE;
EOD);
        // Add answers table
        $this->addSql(<<<EOD
CREATE TABLE answer (
    id INT AUTO_INCREMENT NOT NULL,
    puzzle_id INT NOT NULL,
    team_id INT NOT NULL,
    code TEXT NOT NULL,
    correct TINYINT(1) NOT NULL,
    is_leveling TINYINT(1) NOT NULL,
    answered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX idx_puzzle_id (puzzle_id),
    INDEX idx_team_id (team_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE answer ADD CONSTRAINT fk_answer_puzzle_id FOREIGN KEY (puzzle_id) REFERENCES puzzle (id) ON DELETE CASCADE;
ALTER TABLE answer ADD CONSTRAINT fk_answer_team_id FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE;
EOD);

        // Add extra colum for the team
        $this->addSql('ALTER TABLE team ADD COLUMN current_level_id INT NULL');
        $this->addSql('CREATE INDEX idx_current_level_id ON team (current_level_id);');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT fk_current_level_id FOREIGN KEY (current_level_id) REFERENCES level (id) ON DELETE SET NULL;');

        $this->addSql('ALTER TABLE team ADD COLUMN last_wrong_answer_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\';');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY fk_current_level_id;');
        $this->addSql('ALTER TABLE team DROP COLUMN current_level_id;');
        $this->addSql('ALTER TABLE team DROP COLUMN last_wrong_answer_at;');

        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE password');
        $this->addSql('DROP TABLE puzzle');
        $this->addSql('DROP TABLE level');

        $this->addSql('ALTER TABLE event DROP COLUMN qualification_show_next_attempt_time;');
        $this->addSql('ALTER TABLE event DROP COLUMN qualification_wrong_attempt_penalisation;');
        $this->addSql('ALTER TABLE event DROP COLUMN qualification_show_attempts_count;');
        $this->addSql('ALTER TABLE event DROP COLUMN qualification_max_attempts;');
    }
}
