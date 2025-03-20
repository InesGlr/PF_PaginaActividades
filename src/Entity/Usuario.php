<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Usuario
 *
 * @ORM\Table(name="usuario", uniqueConstraints={@ORM\UniqueConstraint(name="nombre", columns={"nombre"}), @ORM\UniqueConstraint(name="correo", columns={"correo"})})
 * @ORM\Entity
 */
class Usuario implements UserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=20, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="correo", type="string", length=40, nullable=false)
     */
    private $correo;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=30, nullable=false)
     */
    private $clave;

   

     

    /**
     * @var array
     *
     * @ORM\Column(name="rol", type="json", nullable=false)
     */
    private $rol=[];



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getClave(): ?string
    {
        return $this->clave;
    }

    public function setClave(string $clave): self
    {
        $this->clave = $clave;

        return $this;
    }
   
  
    public function getRol(): ?array
    {
        return $this->rol;
    } 
   
    public function setRol($rol): self
{
    if (is_string($rol)) {
        $rol = [$rol];
    }

    // Elimina duplicados
    $this->rol = array_unique($rol);

    return $this;
}


    public function serialize(){
        return serialize(array(
            $this->id,
            $this->nombre,
            $this->clave,
            $this->correo,
        ));
        }

        public function unserialize($serialized){
            list(
                $this->id,
                $this->nombre,
                $this->clave,
                $this->correo,
            ) = unserialize($serialized);
        }

          public function getRoles(){
            return $this->rol;
        }  

        public function getPassword(){
            return $this->getClave();
        }
    
        public function getSalt(){
            return;
        }
    
        public function getUsername(){
            return $this->getNombre();
        }
    
        public function eraseCredentials(){
            return;
        }
 
     
       
        
}


?>