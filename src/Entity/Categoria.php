<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
class Categoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Descripcion = null;

    #[OneToOne(targetEntity: Categoria::class)]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    #[ORM\Column(nullable: true)]
    private ?int $CategoriaID = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): static
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->Descripcion;
    }

    public function setDescripcion(?string $Descripcion): static
    {
        $this->Descripcion = $Descripcion;

        return $this;
    }

    public function getCategoriaID(): ?int
    {
        return $this->CategoriaID;
    }

    public function setCategoriaID(?int $CategoriaID): static
    {
        $this->CategoriaID = $CategoriaID;

        return $this;
    }

}
