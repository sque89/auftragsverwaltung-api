<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181025055112 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE delivery_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job ADD delivery_type_id INT DEFAULT NULL, DROP delivery_type');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8CF52334D FOREIGN KEY (delivery_type_id) REFERENCES delivery_type (id)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F8CF52334D ON job (delivery_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8CF52334D');
        $this->addSql('DROP TABLE delivery_type');
        $this->addSql('DROP INDEX IDX_FBD8E0F8CF52334D ON job');
        $this->addSql('ALTER TABLE job ADD delivery_type SMALLINT NOT NULL, DROP delivery_type_id');
    }
}
