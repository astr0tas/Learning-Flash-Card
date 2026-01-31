<?php

namespace App\Entity;

use App\Config\Constants;
use App\Repository\RecoveryTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecoveryTokenRepository::class)]
#[ORM\Table(name: Constants::TABLES['recovery_token'])]
class RecoveryTokenEntity extends BaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    public string $email;

    #[ORM\Column(type: 'string', length: 255)]
    public string $token;
    #[ORM\Column]
    public \DateTimeImmutable $expiresAt;
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isConsumed = false;
}
