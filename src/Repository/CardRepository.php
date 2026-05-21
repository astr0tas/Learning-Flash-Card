<?php

namespace App\Repository;

use App\Entity\CardEntity;
use Doctrine\Persistence\ManagerRegistry;

class CardRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, CardEntity::class);
  }
}
