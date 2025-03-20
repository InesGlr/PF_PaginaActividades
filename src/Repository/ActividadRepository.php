<?php

// src/Repository/ActividadRepository.php
namespace App\Repository;

use App\Entity\Actividad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ActividadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actividad::class);
    }

    // Obtiene los valores de la columna 'tipo'
    public function listaTipos(): array
    {
        $bd = $this->createQueryBuilder('a')
            ->select('DISTINCT a.tipo') 
            ->orderBy('a.tipo', 'ASC')  
            ->getQuery();

        return array_column($bd->getResult(), 'tipo');
    }

    public function busqueda($valor): array
{
   $bd = $this->createQueryBuilder('a')
       ->leftJoin('a.direccion', 'd')
       ->leftJoin('a.coordinador', 'c')
       ->where('a.estado IN (:estado)')
       ->andWhere('a.nombre LIKE :valor OR d.provincia LIKE :valor OR c.nombre LIKE :valor')
       ->setParameter('estado', ['publicado', 'finalizado', 'cancelado'])
       ->setParameter('valor', '%'.$valor.'%')
       ->getQuery()
       ->getResult();

   return $bd;
}

    
}
?>