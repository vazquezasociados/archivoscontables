<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725010450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE archivo (id INT AUTO_INCREMENT NOT NULL, usuario_alta_id INT NOT NULL, usuario_cliente_asignado_id INT DEFAULT NULL, categoria_id INT DEFAULT NULL, titulo VARCHAR(255) NOT NULL, asignado TINYINT(1) NOT NULL, permitido_publicar TINYINT(1) NOT NULL, fecha_expira DATE DEFAULT NULL, expira TINYINT(1) NOT NULL, descripcion LONGTEXT DEFAULT NULL, url_publica VARCHAR(255) DEFAULT NULL, ocultar_nuevo TINYINT(1) NOT NULL, ocultar_viejo TINYINT(1) NOT NULL, fecha_modificado DATE DEFAULT NULL, total_descarga INT DEFAULT NULL, nombre_archivo VARCHAR(255) DEFAULT NULL, tamaño INT DEFAULT NULL, mime_type VARCHAR(255) DEFAULT NULL, INDEX IDX_3529B482A0753702 (usuario_alta_id), INDEX IDX_3529B482D860B833 (usuario_cliente_asignado_id), INDEX IDX_3529B4823397707A (categoria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE archivo ADD CONSTRAINT FK_3529B482A0753702 FOREIGN KEY (usuario_alta_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE archivo ADD CONSTRAINT FK_3529B482D860B833 FOREIGN KEY (usuario_cliente_asignado_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE archivo ADD CONSTRAINT FK_3529B4823397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archivo DROP FOREIGN KEY FK_3529B482A0753702');
        $this->addSql('ALTER TABLE archivo DROP FOREIGN KEY FK_3529B482D860B833');
        $this->addSql('ALTER TABLE archivo DROP FOREIGN KEY FK_3529B4823397707A');
        $this->addSql('DROP TABLE archivo');
    }
}
