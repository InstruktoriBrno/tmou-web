<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190503064431 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(<<<EOD
CREATE TABLE event (
    id INT AUTO_INCREMENT NOT NULL, 
    number INT NOT NULL,
    name VARCHAR(255) NOT NULL,  
    motto VARCHAR(255) NOT NULL, 
    has_qualification TINYINT(1) NOT NULL, 
    qualification_start DATETIME NULL COMMENT '(DC2Type:datetime_immutable)', 
    qualification_end DATETIME NULL COMMENT '(DC2Type:datetime_immutable)', 
    qualified_team_count INT NULL, 
    event_start DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    event_end DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    total_team_count INT NULL, 
    UNIQUE INDEX unique_number (number), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB;
EOD
);
    }

    public function down(Schema $schema) : void
    {
        $this->addSql(<<<EOD
DROP TABLE event;
EOD
        );
    }
}
