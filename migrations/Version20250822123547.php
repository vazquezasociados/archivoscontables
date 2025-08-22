<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250822123547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archivo ADD notificar_cliente TINYINT(1) NOT NULL, DROP ocultar_nuevo, DROP ocultar_viejo');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archivo ADD ocultar_viejo TINYINT(1) NOT NULL, CHANGE notificar_cliente ocultar_nuevo TINYINT(1) NOT NULL');
    }
}
