<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191004115857 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event ADD COLUMN amount INT NULL;');
        $this->addSql('ALTER TABLE event ADD COLUMN payment_deadline DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\';');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event DROP COLUMN payment_deadline;');
        $this->addSql('ALTER TABLE event DROP COLUMN amount;');
    }
}
