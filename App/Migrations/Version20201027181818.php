<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201027181818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event ADD COLUMN selfreported_entry_fee BOOL NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE team ADD COLUMN selfreported_fee_organization TEXT NULL');
        $this->addSql('ALTER TABLE team ADD COLUMN selfreported_fee_amount INT NULL');
        $this->addSql('ALTER TABLE team ADD COLUMN selfreported_fee_public BOOL NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE event DROP COLUMN selfreported_entry_fee;');
        $this->addSql('ALTER TABLE team DROP COLUMN selfreported_fee_organization;');
        $this->addSql('ALTER TABLE team DROP COLUMN selfreported_fee_amount;');
        $this->addSql('ALTER TABLE team DROP COLUMN selfreported_fee_public;');
    }
}
