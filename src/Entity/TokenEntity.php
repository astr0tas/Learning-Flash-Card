<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class TokenEntity extends BaseEntity
{
  #[ORM\Column(type: 'string', length: 255)]
  public string $email;

  #[ORM\Column(type: 'string', length: 255, nullable: true)]
  public ?string $token = null;
  #[ORM\Column]
  public \DateTimeImmutable $expiresAt;
  #[ORM\Column(type: 'boolean', options: ['default' => false])]
  public bool $isConsumed = false;
}
