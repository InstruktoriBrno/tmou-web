<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200830200000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event DROP COLUMN motto;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event ADD COLUMN motto TINYTEXT NOT NULL');
    }
}
