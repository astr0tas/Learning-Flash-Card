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

  /**
   * Get the value of isConsumed
   *
   * @return bool
   */
  public function getIsConsumed(): bool
  {
    return $this->isConsumed;
  }

  /**
   * Set the value of isConsumed
   *
   * @param bool $isConsumed
   *
   * @return self
   */
  public function setIsConsumed(bool $isConsumed): self
  {
    $this->isConsumed = $isConsumed;

    return $this;
  }

  /**
   * Get the value of expiresAt
   *
   * @return \DateTimeImmutable
   */
  public function getExpiresAt(): \DateTimeImmutable
  {
    return $this->expiresAt;
  }

  /**
   * Set the value of expiresAt
   *
   * @param \DateTimeImmutable $expiresAt
   *
   * @return self
   */
  public function setExpiresAt(\DateTimeImmutable $expiresAt): self
  {
    $this->expiresAt = $expiresAt;

    return $this;
  }

  /**
   * Get the value of token
   *
   * @return ?string
   */
  public function getToken(): ?string
  {
    return $this->token;
  }

  /**
   * Set the value of token
   *
   * @param ?string $token
   *
   * @return self
   */
  public function setToken(?string $token): self
  {
    $this->token = $token;

    return $this;
  }

  /**
   * Get the value of email
   *
   * @return string
   */
  public function getEmail(): string
  {
    return $this->email;
  }

  /**
   * Set the value of email
   *
   * @param string $email
   *
   * @return self
   */
  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }
}
