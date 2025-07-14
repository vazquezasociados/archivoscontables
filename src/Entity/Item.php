<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ItemRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    use TimestampableEntity;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: MemoLineItem::class)]
    private Collection $memoLineItems;
    
    public function __construct()
    {
        $this->memoLineItems = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

        /**
     * @return Collection<int, MemoLineItem>
    */
    public function getMemoLineItems(): Collection
    {
        return $this->memoLineItems;
    }

    public function addMemoLineItem(MemoLineItem $memoLineItem): static
    {
        if (!$this->memoLineItems->contains($memoLineItem)) {
            $this->memoLineItems[] = $memoLineItem;
            $memoLineItem->setItem($this);
        }

        return $this;
    }
    
    public function removeMemoLineItem(MemoLineItem $memoLineItem): static
    {
        if ($this->memoLineItems->removeElement($memoLineItem)) {
            // set the owning side to null (unless already changed)
            if ($memoLineItem->getItem() === $this) {
                $memoLineItem->setItem(null);
            }
        }

        return $this;
    }
    
    public function __toString(): string
    {
        return $this->descripcion ?: 'Sin descripción';
    }
    
}
