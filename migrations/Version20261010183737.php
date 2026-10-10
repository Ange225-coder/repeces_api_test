<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261010183737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recepe (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE recepe_creator (recepe_id INT NOT NULL, creator_id INT NOT NULL, INDEX IDX_FAF876E42E1A626F (recepe_id), INDEX IDX_FAF876E461220EA6 (creator_id), PRIMARY KEY (recepe_id, creator_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE recepe_creator ADD CONSTRAINT FK_FAF876E42E1A626F FOREIGN KEY (recepe_id) REFERENCES recepe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recepe_creator ADD CONSTRAINT FK_FAF876E461220EA6 FOREIGN KEY (creator_id) REFERENCES creator (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recepe_creator DROP FOREIGN KEY FK_FAF876E42E1A626F');
        $this->addSql('ALTER TABLE recepe_creator DROP FOREIGN KEY FK_FAF876E461220EA6');
        $this->addSql('DROP TABLE recepe');
        $this->addSql('DROP TABLE recepe_creator');
    }
}
