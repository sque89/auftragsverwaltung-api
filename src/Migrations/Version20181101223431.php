<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181101223431 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8CF52334D FOREIGN KEY (delivery_type_id) REFERENCES delivery_type (id)');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F89395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F8CF52334D ON job (delivery_type_id)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F89395C3F3 ON job (customer_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8CF52334D');
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F89395C3F3');
        $this->addSql('DROP INDEX IDX_FBD8E0F8CF52334D ON job');
        $this->addSql('DROP INDEX IDX_FBD8E0F89395C3F3 ON job');
    }
}
