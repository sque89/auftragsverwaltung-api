<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181122171351 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE customer (id INT NOT NULL, name VARCHAR(256) NOT NULL, postcode VARCHAR(5) DEFAULT NULL, city VARCHAR(128) DEFAULT NULL, address VARCHAR(128) DEFAULT NULL, contact_person VARCHAR(64) DEFAULT NULL, mail VARCHAR(64) DEFAULT NULL, phone VARCHAR(32) DEFAULT NULL, fax VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job (id VARCHAR(16) NOT NULL, delivery_type_id INT NOT NULL, customer_id INT NOT NULL, date_incoming DATE NOT NULL, date_deadline DATE DEFAULT NULL, description LONGTEXT NOT NULL, notes LONGTEXT DEFAULT NULL, external_purchase LONGTEXT DEFAULT NULL, invoice_number VARCHAR(16) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', version INT DEFAULT 1 NOT NULL, INDEX IDX_FBD8E0F8CF52334D (delivery_type_id), INDEX IDX_FBD8E0F89395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_user (job_id VARCHAR(16) NOT NULL, user_id INT NOT NULL, INDEX IDX_A5FA008BE04EA9 (job_id), INDEX IDX_A5FA008A76ED395 (user_id), PRIMARY KEY(job_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_57698A6A5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setting (id VARCHAR(128) NOT NULL, value LONGTEXT NOT NULL, label VARCHAR(128) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, arranger_id INT NOT NULL, job_id VARCHAR(16) NOT NULL, working_time INT NOT NULL, description LONGTEXT NOT NULL, date DATE NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', version INT DEFAULT 1 NOT NULL, INDEX IDX_527EDB25DE96295 (arranger_id), INDEX IDX_527EDB25BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(25) NOT NULL, password VARCHAR(64) NOT NULL, firstname VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, email VARCHAR(60) NOT NULL, settings LONGTEXT DEFAULT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8CF52334D FOREIGN KEY (delivery_type_id) REFERENCES delivery_type (id)');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F89395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE job_user ADD CONSTRAINT FK_A5FA008BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_user ADD CONSTRAINT FK_A5FA008A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25DE96295 FOREIGN KEY (arranger_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F89395C3F3');
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8CF52334D');
        $this->addSql('ALTER TABLE job_user DROP FOREIGN KEY FK_A5FA008BE04EA9');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25BE04EA9');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE job_user DROP FOREIGN KEY FK_A5FA008A76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25DE96295');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE delivery_type');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE job_user');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_role');
    }
}
