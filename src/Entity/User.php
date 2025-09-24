<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_CUIT', fields: ['nombreUsuario'])]
#[UniqueEntity(
    fields: ['email'],
    message: 'Este email ya está registrado en el sistema.'
)]
#[UniqueEntity(
    fields: ['nombreUsuario'],
    message: 'Este CUIT/CUIL ya está registrado en el sistema.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
   
    // Usa el trait
    use TimestampableEntity;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;
   
    /**
     * @var string|null The plain password (not persisted)
     */
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $nombreUsuario = null;

    #[ORM\Column(length: 350, nullable: true)]
    private ?string $direccion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(length: 255)]
    private ?string $nombreContactoInterno = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $fechaDuplicado = null;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Memo::class)]
    private Collection $memos;

    /**
     * @var Collection<int, Archivo>
     */
    #[ORM\OneToMany(targetEntity: Archivo::class, mappedBy: 'usuario_alta')]
    private Collection $archivos;

    #[ORM\Column]
    private ?bool $activo = true;

    #[ORM\Column]
    private ?bool $enviarCorreoBienvenido = true;
    
    public function __construct()
    {
        $this->memos = new ArrayCollection();
        $this->archivos = new ArrayCollection();
         $this->roles = ['ROLE_USER'];
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // /**
    //  * A visual identifier that represents this user.
    //  *
    //  * @see UserInterface
    //  */
    // public function getUserIdentifier(): string
    // {
    //     return (string) $this->email;
    // }

     /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->nombreUsuario; // Cambio clave aquí
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
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

    public function getNombreUsuario(): ?string
    {
        return $this->nombreUsuario;
    }

    public function setNombreUsuario(string $nombreUsuario): static
    {
        $this->nombreUsuario = $nombreUsuario;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getNombreContactoInterno(): ?string
    {
        return $this->nombreContactoInterno;
    }

    public function setNombreContactoInterno(string $nombreContactoInterno): static
    {
        $this->nombreContactoInterno = $nombreContactoInterno;

        return $this;
    }

    public function getFechaDuplicado(): ?\DateTime
    {
        return $this->fechaDuplicado;
    }

    public function setFechaDuplicado(?\DateTime $fechaDuplicado): static
    {
        $this->fechaDuplicado = $fechaDuplicado;

        return $this;
    }

     /**
     * @return Collection<int, Memo>
     */
    public function getMemos(): Collection
    {
        return $this->memos;
    }

    public function addMemo(Memo $memo): static
    {
        if (!$this->memos->contains($memo)) {
            $this->memos->add($memo);
            $memo->setUsuario($this);
        }
        return $this;
    }

    public function removeMemo(Memo $memo): static
    {
        if ($this->memos->removeElement($memo)) {
            // Limpia la relación solo si es necesario
            if ($memo->getUsuario() === $this) {
                $memo->setUsuario(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nombre ?: 'Sin nombre';
    }

    /**
     * @return Collection<int, Archivo>
     */
    public function getArchivos(): Collection
    {
        return $this->archivos;
    }

    public function addArchivo(Archivo $archivo): static
    {
        if (!$this->archivos->contains($archivo)) {
            $this->archivos->add($archivo);
            $archivo->setUsuarioAlta($this);
        }

        return $this;
    }

    public function removeArchivo(Archivo $archivo): static
    {
        if ($this->archivos->removeElement($archivo)) {
            // set the owning side to null (unless already changed)
            if ($archivo->getUsuarioAlta() === $this) {
                $archivo->setUsuarioAlta(null);
            }
        }

        return $this;
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    public function isEnviarCorreoBienvenido(): ?bool
    {
        return $this->enviarCorreoBienvenido;
    }

    public function setEnviarCorreoBienvenido(bool $enviarCorreoBienvenido): static
    {
        $this->enviarCorreoBienvenido = $enviarCorreoBienvenido;

        return $this;
    }

    /**
     * Get the value of enviarCorreoBienvenido
     */ 
    public function getEnviarCorreoBienvenido()
    {
        return $this->enviarCorreoBienvenido;
    }

    /**
     * Cuenta los archivos asignados a este usuario
    */
    public function getArchivosAsignadosCount(): int
    {
        return $this->archivos->filter(function($archivo) {
            return $archivo->getUsuarioClienteAsignado() === $this;
        })->count();
    }

    /**
     * Obtiene los archivos asignados a este usuario
     */
    public function getArchivosAsignados(): Collection
    {
        return $this->archivos->filter(function($archivo) {
            return $archivo->getUsuarioClienteAsignado() === $this;
        });
    }

    /**
     * Método virtual para EasyAdmin - botón de acciones  
     */
    public function getVerArchivos(): string
    {
        // Este método tampoco es práctico porque no tiene acceso al AdminUrlGenerator
        return '';
    }
}
