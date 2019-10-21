<?php declare(strict_types=1);

namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191021171644 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team_sso_session ADD COLUMN jwt TEXT NULL;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE team_sso_session DROP COLUMN jwt;');
    }
}
