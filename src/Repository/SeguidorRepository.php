<?php 
// src/Repository/SeguidorRepository.php

namespace App\Repository;

use App\Entity\Seguidor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SeguidorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seguidor::class);
    }

    // Contar el número de seguidores de un usuario
    public function countFollowers($usuario)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.usuario = :usuario')
            ->setParameter('usuario', $usuario)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Contar el número de personas a las que el usuario sigue
    public function countFollowing($usuario)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.follower = :usuario')
            ->setParameter('usuario', $usuario)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
