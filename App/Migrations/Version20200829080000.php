<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200829080000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE post ADD COLUMN nickname TEXT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE post DROP COLUMN nickname;');
    }
}
