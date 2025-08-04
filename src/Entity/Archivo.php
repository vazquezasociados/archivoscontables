<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use App\Repository\ArchivoRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ArchivoRepository::class)]
#[Vich\Uploadable]
class Archivo
{
    // Usa el trait
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titulo = null;

    #[ORM\Column]
    private ?bool $asignado = null;

    #[ORM\Column]
    private ?bool $permitido_publicar = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $fecha_expira = null;

    #[ORM\Column]
    private ?bool $expira = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url_publica = null;

    #[ORM\Column]
    private ?bool $ocultar_nuevo = null;

    #[ORM\Column]
    private ?bool $ocultar_viejo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $fecha_modificado = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_descarga = null;

    #[ORM\ManyToOne(inversedBy: 'archivos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usuario_alta = null;

    #[ORM\ManyToOne]
    private ?User $usuario_cliente_asignado = null;

    #[ORM\ManyToOne]
    private ?Categoria $categoria = null;
    
    #[Vich\UploadableField(mapping: 'archivos', fileNameProperty: 'nombreArchivo', size: 'tamaño')]   
    private ?File $archivoFile = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre_archivo = null;

    #[ORM\Column(nullable: true)]
    private ?int $tamaño = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mimeType = null;

    public function __construct()
    {
        $this->asignado = false;
        $this->permitido_publicar = false;
        $this->expira = false;
        $this->ocultar_nuevo = false;
        $this->ocultar_viejo = false;
    }

    /**
     * @param File|UploadedFile|null $archivo
    */
    public function setArchivoFile(?File $file = null): void
    {
        $this->archivoFile = $file;

        if (null !== $file) {
            $this->updateAt = new \DateTimeImmutable();           
        }
    }

    public function getArchivoFile(): ?File
    {
        return $this->archivoFile;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function isAsignado(): ?bool
    {
        return $this->asignado;
        // return $this->getUsuarioClienteAsignado() !== null;
    }

    public function setAsignado(bool $asignado): static
    {
        $this->asignado = $asignado;

        return $this;
    }

    public function getAsignadoTexto(): string
    {
        return $this->getUsuarioClienteAsignado() !== null
            ? '<i class="fas fa-check-circle text-success"></i>'
            : '<i class="fas fa-times-circle text-danger"></i>';
    }

    public function isPermitidoPublicar(): ?bool
    {
        return $this->permitido_publicar;
    }

    public function setPermitidoPublicar(bool $permitido_publicar): static
    {
        $this->permitido_publicar = $permitido_publicar;

        return $this;
    }

    public function getFechaExpira(): ?\DateTime
    {
        return $this->fecha_expira;
    }

    public function setFechaExpira(?\DateTime $fecha_expira): static
    {
        $this->fecha_expira = $fecha_expira;

        return $this;
    }

    public function isExpira(): ?bool
    {
        return $this->expira;
    }

    public function setExpira(bool $expira): static
    {
        $this->expira = $expira;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getUrlPublica(): ?string
    {
        return $this->url_publica;
    }

    public function setUrlPublica(?string $url_publica): static
    {
        $this->url_publica = $url_publica;

        return $this;
    }

    public function isOcultarNuevo(): ?bool
    {
        return $this->ocultar_nuevo;
    }

    public function setOcultarNuevo(bool $ocultar_nuevo): static
    {
        $this->ocultar_nuevo = $ocultar_nuevo;

        return $this;
    }

    public function isOcultarViejo(): ?bool
    {
        return $this->ocultar_viejo;
    }

    public function setOcultarViejo(bool $ocultar_viejo): static
    {
        $this->ocultar_viejo = $ocultar_viejo;

        return $this;
    }

    public function getFechaModificado(): ?\DateTime
    {
        return $this->fecha_modificado;
    }

    public function setFechaModificado(?\DateTime $fecha_modificado): static
    {
        $this->fecha_modificado = $fecha_modificado;

        return $this;
    }

    public function getTotalDescarga(): ?int
    {
        return $this->total_descarga;
    }

    public function setTotalDescarga(?int $total_descarga): static
    {
        $this->total_descarga = $total_descarga;

        return $this;
    }

    public function getUsuarioAlta(): ?User
    {
        return $this->usuario_alta;
    }

    public function setUsuarioAlta(?User $usuario_alta): static
    {
        $this->usuario_alta = $usuario_alta;

        return $this;
    }

    public function getUsuarioClienteAsignado(): ?User
    {
        return $this->usuario_cliente_asignado;
    }

    public function setUsuarioClienteAsignado(?User $usuario_cliente_asignado): static
    {
        $this->usuario_cliente_asignado = $usuario_cliente_asignado;

        // Actualiza el estado de asignado llamando al setter
        $this->setAsignado($usuario_cliente_asignado !== null);
         
        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getNombreArchivo(): ?string
    {
        return $this->nombre_archivo;
    }

    public function setNombreArchivo(?string $nombre_archivo): static
    {
        $this->nombre_archivo = $nombre_archivo;

        return $this;
    }

    public function getTamaño(): ?int
    {
        return $this->tamaño;
    }

    public function setTamaño(?int $tamaño): static
    {
        $this->tamaño = $tamaño;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }
}
