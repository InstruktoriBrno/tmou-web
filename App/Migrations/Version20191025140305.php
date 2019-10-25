<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191025140305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE menu_item ADD COLUMN for_anonymous TINYINT(1) NOT NULL DEFAULT FALSE;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE menu_item DROP COLUMN for_anonymous;');
    }
}
