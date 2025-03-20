<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Seguidor
 *
 * @ORM\Table(name="seguidor", indexes={@ORM\Index(name="fk_seguidor", columns={"follower"}), @ORM\Index(name="fk_usuario", columns={"usuario"})})
 * @ORM\Entity
 */
class Seguidor
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
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario", referencedColumnName="id")
     * })
     */
    private $usuario;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="follower", referencedColumnName="id")
     * })
     */
    private $follower;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getFollower(): ?Usuario
    {
        return $this->follower;
    }

    public function setFollower(?Usuario $follower): self
    {
        $this->follower = $follower;

        return $this;
    }


}
