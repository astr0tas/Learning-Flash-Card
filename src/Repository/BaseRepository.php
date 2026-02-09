<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class BaseRepository extends ServiceEntityRepository
{
  protected $entityManager;

  public function __construct(ManagerRegistry $registry, string $entityClass)
  {
    parent::__construct($registry, $entityClass);

    $this->entityManager = $this->getEntityManager();
  }
}
