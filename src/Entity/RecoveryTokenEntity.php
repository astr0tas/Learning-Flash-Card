<?php

namespace App\Entity;

use App\Config\Constants;
use App\Repository\RecoveryTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecoveryTokenRepository::class)]
#[ORM\Table(name: Constants::TABLE_RECOVERY_TOKEN)]
class RecoveryTokenEntity extends TokenEntity {}
