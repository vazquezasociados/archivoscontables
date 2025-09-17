<?php

// src/Dto/ArchivoCollectionDto.php
namespace App\Dto;

use App\Entity\Archivo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ArchivoCollectionDto
{
    private Collection $archivos;

    public function __construct()
    {
        $this->archivos = new ArrayCollection();
    }

    public function getArchivos(): Collection
    {
        return $this->archivos;
    }

    public function setArchivos(Collection $archivos): self
    {
        $this->archivos = $archivos;
        return $this;
    }

    public function addArchivo(Archivo $archivo): self
    {
        if (!$this->archivos->contains($archivo)) {
            $this->archivos[] = $archivo;
        }
        return $this;
    }

    public function removeArchivo(Archivo $archivo): self
    {
        $this->archivos->removeElement($archivo);
        return $this;
    }
}