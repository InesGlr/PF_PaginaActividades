<?php

// src/Repository/SesionRepository.php
namespace App\Repository;

use App\Entity\Sesion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SesionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sesion::class);
    }

   
    public function actividadesPasadas($fechaActual): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.fecha < :fechaActual OR (s.fecha = :fechaActual AND s.hora <= :horaActual)')
            ->setParameter('fechaActual', $fechaActual->format('Y-m-d'))
            ->setParameter('horaActual', $fechaActual->format('H:i:s'))
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