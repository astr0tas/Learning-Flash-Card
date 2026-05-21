<?php

namespace App\Repository;

use App\Entity\UserEntity;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, UserEntity::class);
  }
}
