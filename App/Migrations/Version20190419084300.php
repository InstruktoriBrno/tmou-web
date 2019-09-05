<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190419084300 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(<<<EOD
CREATE TABLE organizator (
    id INT AUTO_INCREMENT NOT NULL,
    given_name TINYTEXT NOT NULL,
    family_name TINYTEXT NOT NULL,
    username TINYTEXT NOT NULL, 
    email TINYTEXT NOT NULL, 
    last_login DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    keycloak_key BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary)', 
    role VARCHAR(128) NULL COMMENT '(DC2Type:organizator_role)',
    UNIQUE INDEX unique_keycloak_key (keycloak_key),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
EOD
);
    }

    public function down(Schema $schema) : void
    {
        $this->addSql(<<<EOD
DROP TABLE organizator;
EOD
);
    }
}
