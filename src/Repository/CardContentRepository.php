<?php

namespace App\Repository;

use App\Entity\CardContentEntity;
use Doctrine\Persistence\ManagerRegistry;

class CardContentRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, CardContentEntity::class);
  }
}
