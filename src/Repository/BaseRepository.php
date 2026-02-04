<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

abstract class BaseRepository extends ServiceEntityRepository
{
  public function getEntityManager(): EntityManagerInterface
  {
    return parent::getEntityManager();
  }
}
