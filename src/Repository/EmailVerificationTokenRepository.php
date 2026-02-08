<?php

namespace App\Repository;

use App\Entity\EmailVerificationTokenEntity;
use Doctrine\Persistence\ManagerRegistry;

class EmailVerificationTokenRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, EmailVerificationTokenEntity::class);
  }
}
