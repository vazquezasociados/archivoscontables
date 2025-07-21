<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MemoRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: MemoRepository::class)]
class Memo
{
       // Usa el trait
    use TimestampableEntity;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\ManyToOne(inversedBy: 'memos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usuario = null;

    #[ORM\OneToMany(mappedBy: 'memo', targetEntity: MemoLineItem::class, cascade: ['persist'],orphanRemoval: true)]
    private Collection $lineItems;

    public function __construct()
    {
        $this->lineItems = new ArrayCollection();
        $this->estado = 'Retira el cliente'; // Valor por defecto
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

/**
 * @return Collection<int, MemoLineItem>
 */
public function getLineItems(): Collection
{
    return $this->lineItems;
}

public function addLineItem(MemoLineItem $lineItem): static
{
    if (!$this->lineItems->contains($lineItem)) {
        $this->lineItems->add($lineItem);
        $lineItem->setMemo($this);
    }
    return $this;
}

public function removeLineItem(MemoLineItem $lineItem): static
{
    if ($this->lineItems->removeElement($lineItem)) {
        // set the owning side to null (unless already changed)
        if ($lineItem->getMemo() === $this) {
            $lineItem->setMemo(null);
        }
    }
    return $this;
}

public function getPdfDownloadLink(): string
{
    return ''; // Esto es solo un placeholder
}

}
