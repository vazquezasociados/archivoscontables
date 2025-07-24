<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250722022812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categoria DROP FOREIGN KEY FK_4E10122D613CEC58');
        $this->addSql('ALTER TABLE categoria CHANGE descripcion descripcion LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE categoria ADD CONSTRAINT FK_4E10122D613CEC58 FOREIGN KEY (padre_id) REFERENCES categoria (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categoria DROP FOREIGN KEY FK_4E10122D613CEC58');
        $this->addSql('ALTER TABLE categoria CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE categoria ADD CONSTRAINT FK_4E10122D613CEC58 FOREIGN KEY (padre_id) REFERENCES categoria (id)');
    }
}
