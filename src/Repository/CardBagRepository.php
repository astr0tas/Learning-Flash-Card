<?php

namespace App\Repository;

use App\Entity\CardBagEntity;
use Doctrine\Persistence\ManagerRegistry;

class CardBagRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, CardBagEntity::class);
  }
}
