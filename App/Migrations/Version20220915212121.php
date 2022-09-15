<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220915212121 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event ADD COLUMN after_registration_team_game_status VARCHAR(128) NOT NULL DEFAULT \'REGISTERED\' COMMENT \'(DC2Type:game_status)\';');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event DROP COLUMN after_registration_team_game_status;');
    }
}
