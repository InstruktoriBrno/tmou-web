<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190529173041 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(<<<EOD
CREATE TABLE page (
    id INT AUTO_INCREMENT NOT NULL, 
    event_id INT DEFAULT NULL, 
    title LONGTEXT NOT NULL, 
    slug VARCHAR(255) NOT NULL, 
    heading LONGTEXT NOT NULL, 
    content LONGTEXT NOT NULL, 
    hidden TINYINT(1) NOT NULL, 
    reveal_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', 
    last_updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', 
    is_default TINYINT(1) NOT NULL, 
    INDEX idx_event_id (event_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB;
ALTER TABLE page ADD CONSTRAINT fk_event_id FOREIGN KEY (event_id) REFERENCES event (id);
EOD
        );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql(<<<EOD
DROP TABLE page;
EOD
        );
    }
}
