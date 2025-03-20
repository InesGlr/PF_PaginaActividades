<?php

// src/Repository/UsuarioRepository.php
namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

   
     public function listaAdministradores(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.rol LIKE :rol')
            ->andWhere('u.rol NOT LIKE :rolSuperAdmin')
            ->setParameter('rol', '%"ROLE_ADMIN"%')
            ->setParameter('rolSuperAdmin', '%"ROLE_SUPER_ADMIN"%')
            ->orderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult(); 
    }

    public function busqueda($valor): array
    {
       $bd = $this->createQueryBuilder('u')
           ->andWhere('u.nombre LIKE :valor or u.correo LIKE :valor')
           ->setParameter('valor', '%'.$valor.'%')
           ->getQuery()
           ->getResult();
    
       return $bd;
    }
}
?>