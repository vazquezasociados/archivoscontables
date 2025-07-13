<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713151124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD nombre VARCHAR(255) NOT NULL, ADD nombre_usuario BIGINT NOT NULL, ADD direccion VARCHAR(350) DEFAULT NULL, ADD telefono VARCHAR(255) DEFAULT NULL, ADD nombre_contacto_interno VARCHAR(255) NOT NULL, ADD fecha_duplicado DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP nombre, DROP nombre_usuario, DROP direccion, DROP telefono, DROP nombre_contacto_interno, DROP fecha_duplicado');
    }
}
