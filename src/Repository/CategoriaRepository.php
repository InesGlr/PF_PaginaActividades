<?php

// src/Repository/CategoriaRepository.php
namespace App\Repository;

use App\Entity\Categoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class CategoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoria::class);
    }

   public function listaCategorias(): array
    {
         $bd = $this->createQueryBuilder('c')
            ->select('c.nombre') 
            ->orderBy('c.nombre', 'ASC') 
            ->getQuery();

        return array_column($bd->getResult(), 'categoria');
    }
}
?>