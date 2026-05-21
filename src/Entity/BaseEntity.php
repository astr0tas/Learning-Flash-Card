<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks] // Vital for triggering PrePersist/PreUpdate
abstract class BaseEntity
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column]
  private ?\DateTimeImmutable $createdAt = null;

  #[ORM\Column(nullable: true)]
  private ?\DateTimeImmutable $updatedAt = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  #[ORM\PrePersist]
  public function onPrePersist(): void
  {
    $this->createdAt = new \DateTimeImmutable();
  }

  #[ORM\PreUpdate]
  public function onPreUpdate(): void
  {
    $this->updatedAt = new \DateTimeImmutable();
  }

  /**
   * Get the value of createdAt
   *
   * @return ?\DateTimeImmutable
   */
  public function getCreatedAt(): ?\DateTimeImmutable
  {
    return $this->createdAt;
  }

  /**
   * Set the value of createdAt
   *
   * @param ?\DateTimeImmutable $createdAt
   *
   * @return self
   */
  public function setCreatedAt(?\DateTimeImmutable $createdAt): self
  {
    $this->createdAt = $createdAt;

    return $this;
  }

  /**
   * Get the value of updatedAt
   *
   * @return ?\DateTimeImmutable
   */
  public function getUpdatedAt(): ?\DateTimeImmutable
  {
    return $this->updatedAt;
  }

  /**
   * Set the value of updatedAt
   *
   * @param ?\DateTimeImmutable $updatedAt
   *
   * @return self
   */
  public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }
}
