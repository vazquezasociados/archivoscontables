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
    private ?string $Nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Descripcion = null;

    // Relación ManyToOne: Una subcategoría tiene un único padre.
    // 'targetEntity: Categoria::class' apunta a la misma entidad.
    // 'inversedBy: "subcategorias"' indica la propiedad en el lado inverso de la relación (OneToMany).
    // 'JoinColumn' define la columna de clave foránea. 'nullable: true' permite categorías sin padre (raíz).
    #[ORM\ManyToOne(targetEntity: Categoria::class, inversedBy: 'subcategorias')]
    #[ORM\JoinColumn(name: 'padre_id', referencedColumnName: 'id', nullable: true)]
    private ?Categoria $padre = null;

    // Relación OneToMany: Un padre puede tener muchas subcategorías.
    // 'mappedBy: "padre"' indica que la relación es definida por la propiedad 'padre' en el lado ManyToOne.
    // 'cascade: ["persist", "remove"]' asegura que las operaciones de persistencia/eliminación se propaguen a las subcategorías.
    #[ORM\OneToMany(mappedBy: 'padre', targetEntity: Categoria::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
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
        return $this->Nombre ?: 'Sin descripción';
    }
}
