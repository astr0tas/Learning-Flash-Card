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
  protected ?\DateTimeImmutable $createdAt = null;

  #[ORM\Column(nullable: true)]
  protected ?\DateTimeImmutable $updatedAt = null;

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
}
