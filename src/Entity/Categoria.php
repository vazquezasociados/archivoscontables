<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection; 
use Doctrine\Common\Collections\Collection;    

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
class Categoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descripcion = null;

    // Relación recursiva: Una categoría puede tener un padre
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subcategorias')]
    #[ORM\JoinColumn(name: 'padre_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $padre = null;

    // Una categoría puede tener muchas subcategorías
    #[ORM\OneToMany(mappedBy: 'padre', targetEntity: self::class, cascade: ['persist'], orphanRemoval: false)]
    private Collection $subcategorias;
    
    public function __construct()
    {
        $this->subcategorias = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

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

    public function getPadre(): ?self
    {
        return $this->padre;
    }

    public function setPadre(?self $padre): static
    {
        $this->padre = $padre;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubcategorias(): Collection
    {
        return $this->subcategorias;
    }

    public function addSubcategoria(self $subcategoria): static
    {
        if (!$this->subcategorias->contains($subcategoria)) {
            $this->subcategorias->add($subcategoria);
            $subcategoria->setPadre($this);
        }

        return $this;
    }

    public function removeSubcategoria(self $subcategoria): static
    {
        if ($this->subcategorias->removeElement($subcategoria)) {
            // set the owning side to null (unless already changed)
            if ($subcategoria->getPadre() === $this) {
                $subcategoria->setPadre(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nombre ?: 'Sin nombre';
    }
}
