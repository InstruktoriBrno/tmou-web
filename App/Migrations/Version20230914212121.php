<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230914212121 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team ADD COLUMN can_change_game_time BOOL NOT NULL DEFAULT FALSE;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team DROP COLUMN can_change_game_time;');
    }
}
