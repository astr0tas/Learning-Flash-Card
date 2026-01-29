<?php

namespace App\Repository;

use App\Entity\RecoveryTokenEntity;
use Doctrine\Persistence\ManagerRegistry;

class RecoveryTokenRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecoveryTokenEntity::class);
    }
}
