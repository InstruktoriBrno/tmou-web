<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200904093000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE thread ADD COLUMN reveal_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE thread DROP COLUMN reveal_at;');
    }
}
