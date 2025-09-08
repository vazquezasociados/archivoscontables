<?php

namespace App\Entity;

use App\Repository\MemoLineItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MemoLineItemRepository::class)]
class MemoLineItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $descripcionAdicional = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\Type(\DateTimeInterface::class, message: "El valor del periodo no es una fecha válida.")] 
    private ?\DateTimeInterface $periodo = null; 

    #[ORM\ManyToOne(inversedBy: 'lineItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Memo $memo = null;

    #[ORM\ManyToOne(inversedBy: 'memoLineItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Debes seleccionar un ítem.")] // Valida que el Item esté seleccionado
    private ?Item $item = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcionAdicional(): ?string
    {
        return $this->descripcionAdicional;
    }

    public function setDescripcionAdicional(?string $descripcionAdicional): static
    {
        $this->descripcionAdicional = $descripcionAdicional;

        return $this;
    }

    public function getPeriodo(): ?\DateTime
    {
        return $this->periodo;
    }

    public function setPeriodo(?\DateTime $periodo): static
    {
        $this->periodo = $periodo;

        return $this;
    }

    public function getMemo(): ?Memo
    {
        return $this->memo;
    }

    public function setMemo(?Memo $memo): static
    {
        $this->memo = $memo;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): static
    {
        $this->item = $item;

        return $this;
    }
    
    public function __toString(): string
    {
        return $this->descripcionAdicional ?: 'Sin descripción adicional';
    }
}
