<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201027191919 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event ADD COLUMN sorting DOUBLE NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event DROP COLUMN sorting;');
    }
}
