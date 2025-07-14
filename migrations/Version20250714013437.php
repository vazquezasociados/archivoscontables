<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714013437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, descripcion VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE memo (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, estado VARCHAR(255) NOT NULL, INDEX IDX_AB4A902ADB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE memo_line_item (id INT AUTO_INCREMENT NOT NULL, memo_id INT NOT NULL, item_id INT NOT NULL, descripcion_adicional VARCHAR(300) NOT NULL, periodo DATE NOT NULL, INDEX IDX_B6D1FF4CB4D32439 (memo_id), INDEX IDX_B6D1FF4C126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902ADB38439E FOREIGN KEY (usuario_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE memo_line_item ADD CONSTRAINT FK_B6D1FF4CB4D32439 FOREIGN KEY (memo_id) REFERENCES memo (id)');
        $this->addSql('ALTER TABLE memo_line_item ADD CONSTRAINT FK_B6D1FF4C126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902ADB38439E');
        $this->addSql('ALTER TABLE memo_line_item DROP FOREIGN KEY FK_B6D1FF4CB4D32439');
        $this->addSql('ALTER TABLE memo_line_item DROP FOREIGN KEY FK_B6D1FF4C126F525E');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE memo');
        $this->addSql('DROP TABLE memo_line_item');
    }
}
