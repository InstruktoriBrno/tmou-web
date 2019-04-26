<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190419084300 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<EOD
CREATE TABLE organizator (
    id INT AUTO_INCREMENT NOT NULL,
    given_name VARCHAR(255) NOT NULL,
    family_name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL, 
    email VARCHAR(255) NOT NULL, 
    last_login DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    keycloak_key BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary)', 
    role VARCHAR(128) NULL COMMENT '(DC2Type:organizatorType)',
    UNIQUE INDEX unique_keycloak_key (keycloak_key),
    PRIMARY KEY(id)
) ENGINE = InnoDB;
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
