<?php

namespace App\Entity;

use App\Config\Constants;
use App\Repository\CardBagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CardBagRepository::class)]
#[ORM\Table(name: Constants::TABLE_CARD_BAG)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class CardBagEntity extends BaseEntity
{
  #[ORM\Column(type: 'string', length: 255)]
  private string $title;

  #[ORM\Column(type: 'string', length: 1000)]
  private string $description;

  #[ORM\ManyToOne(targetEntity: UserEntity::class)]
  #[ORM\JoinColumn(name: 'user_id', nullable: false)]
  private ?UserEntity $userEntity = null;

  #[ORM\OneToMany(targetEntity: CardEntity::class, mappedBy: 'cardBagEntity')]
  private Collection $cardEntities;

  // 1. THE OWNING SIDE (Who is my parent?)
  // nullable: true is important here! A top-level item won't have a parent.
  #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childrenCardBagEntities')]
  #[ORM\JoinColumn(name: 'parent_card_bag_id', referencedColumnName: 'id', nullable: true)]
  private ?self $parentCardBagEntity = null;

  // 2. THE INVERSE SIDE (Who are my children?)
  #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentCardBagEntity')]
  private Collection $childrenCardBagEntities;

  #[ORM\Column(type: 'datetime', nullable: true)]
  private ?\DateTimeInterface $deletedAt = null;

  /**
   * Set the value of deletedAt
   *
   * @param ?\DateTimeInterface $deletedAt
   *
   * @return self
   */
  public function setDeletedAt(?\DateTimeInterface $deletedAt): self
  {
    $this->deletedAt = $deletedAt;

    return $this;
  }

  /**
   * Get the value of description
   *
   * @return string
   */
  public function getDescription(): string
  {
    return $this->description;
  }

  /**
   * Set the value of description
   *
   * @param string $description
   *
   * @return self
   */
  public function setDescription(string $description): self
  {
    $this->description = $description;

    return $this;
  }

  /**
   * Get the value of title
   *
   * @return string
   */
  public function getTitle(): string
  {
    return $this->title;
  }

  /**
   * Set the value of title
   *
   * @param string $title
   *
   * @return self
   */
  public function setTitle(string $title): self
  {
    $this->title = $title;

    return $this;
  }

  /**
   * Get the value of userEntity
   *
   * @return ?UserEntity
   */
  public function getUserEntity(): ?UserEntity
  {
    return $this->userEntity;
  }

  /**
   * Set the value of userEntity
   *
   * @param ?UserEntity $userEntity
   *
   * @return self
   */
  public function setUserEntity(?UserEntity $userEntity): self
  {
    $this->userEntity = $userEntity;

    return $this;
  }

  /**
   * Get the value of cardEntities
   *
   * @return Collection
   */
  public function getCardEntities(): Collection
  {
    return $this->cardEntities;
  }

  public function addCard(CardEntity $card): self
  {
    if (!$this->cardEntities->contains($card)) {
      $this->cardEntities->add($card);

      // Keep the relationship in sync!
      // When you add B to A, you must tell B that A is its owner.
      $card->setCardBagEntity($this);
    }

    return $this;
  }

  public function removeCard(CardEntity $card): self
  {
    if ($this->cardEntities->removeElement($card)) {
      // Set the owning side to null (unless already changed)
      if ($card->getCardBagEntity() === $this) {
        $card->setCardBagEntity(null);
      }
    }

    return $this;
  }

  /**
   * Get the value of parentCardBagEntity
   *
   * @return ?self
   */
  public function getParentCardBagEntity(): ?self
  {
    return $this->parentCardBagEntity;
  }

  /**
   * Set the value of parentCardBagEntity
   *
   * @param ?self $parentCardBagEntity
   *
   * @return self
   */
  public function setParentCardBagEntity(?self $parentCardBagEntity): self
  {
    $this->parentCardBagEntity = $parentCardBagEntity;

    return $this;
  }

  /**
   * Get the value of childrenCardBagEntities
   *
   * @return Collection
   */
  public function getChildrenCardBagEntities(): Collection
  {
    return $this->childrenCardBagEntities;
  }

  public function addChildCardBagEntity(self $childCardBagEntity): self
  {
    if (!$this->childrenCardBagEntities->contains($childCardBagEntity)) {
      $this->childrenCardBagEntities->add($childCardBagEntity);
      // Sync the relationship!
      $childCardBagEntity->setParentCardBagEntity($this);
    }

    return $this;
  }

  public function removeChildCardBagEntity(self $childCardBagEntity): self
  {
    if ($this->childrenCardBagEntities->removeElement($childCardBagEntity)) {
      // set the owning side to null (unless already changed)
      if ($childCardBagEntity->getParentCardBagEntity() === $this) {
        $childCardBagEntity->setParentCardBagEntity(null);
      }
    }

    return $this;
  }
}
