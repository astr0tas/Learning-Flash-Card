<?php

namespace App\Repository;

use App\Entity\TopicEntity;
use Doctrine\Persistence\ManagerRegistry;

class TopicRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, TopicEntity::class);
  }
}
