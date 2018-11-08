<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181106200107 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE task CHANGE arranger_id arranger_id INT NOT NULL, CHANGE job_id job_id VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25DE96295 FOREIGN KEY (arranger_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25DE96295 ON task (arranger_id)');
        $this->addSql('CREATE INDEX IDX_527EDB25BE04EA9 ON task (job_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25DE96295');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25BE04EA9');
        $this->addSql('DROP INDEX IDX_527EDB25DE96295 ON task');
        $this->addSql('DROP INDEX IDX_527EDB25BE04EA9 ON task');
        $this->addSql('ALTER TABLE task CHANGE arranger_id arranger_id INT DEFAULT NULL, CHANGE job_id job_id VARCHAR(16) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
