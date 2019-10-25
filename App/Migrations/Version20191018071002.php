<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191018071002 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE menu_item (
            id INT AUTO_INCREMENT NOT NULL, 
            event_id INT NULL,
            content TEXT NOT NULL,
            title TEXT NULL,
            class TEXT NULL,
            tag TEXT NULL,
            label TEXT NULL,
            weight INT NOT NULL DEFAULT 0,
            target_page_id INT NULL,
            target_event_id INT NULL,
            target_slug TEXT NULL,
            target_url TEXT NULL,
            for_organizators TINYINT(1) NOT NULL DEFAULT FALSE,
            for_teams TINYINT(1) NOT NULL DEFAULT FALSE,
            reveal_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\',
            hide_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            INDEX menu_item_event_id (event_id), 
            INDEX menu_item_target_page_id (target_page_id), 
            INDEX menu_item_target_event_id (target_event_id), 
            PRIMARY KEY(id),
            CHECK(
                (target_page_id IS NOT NULL AND target_event_id IS NULL AND target_slug IS NULL AND target_url IS NULL) OR
                (target_page_id IS NULL AND target_event_id IS NULL AND target_slug IS NULL AND target_url IS NOT NULL) OR
                (target_page_id IS NULL AND target_event_id IS NOT NULL AND target_slug IS NOT NULL AND target_url IS NULL)
            )
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT menu_item_event_id FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT menu_item_target_page_id FOREIGN KEY (target_page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT menu_item_target_event_id FOREIGN KEY (target_event_id) REFERENCES event (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE menu_item');
    }
}
