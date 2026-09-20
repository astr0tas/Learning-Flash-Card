<?php

namespace App\Entity;

use App\Config\Constants;
use App\Config\Constraints;
use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: TopicRepository::class)]
#[ORM\Table(name: Constants::TABLE_TOPIC)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class TopicEntity extends BaseEntity
{
  #[ORM\Column(type: 'string', length: Constraints::TOPIC_NAME_MAX_LENGTH)]
  private string $name;

  #[ORM\ManyToOne(targetEntity: UserEntity::class, inversedBy: 'topicEntities')]
  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  private ?UserEntity $userEntity = null;

  #[ORM\OneToMany(targetEntity: CardEntity::class, mappedBy: 'topicEntity', cascade: ['remove'])]
  private Collection $cardEntities;

  // 1. THE OWNING SIDE (Who is my parent?)
  // nullable: true is important here! A top-level item won't have a parent.
  #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childrenTopicEntities')]
  #[ORM\JoinColumn(name: 'parent_topic_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
  private ?self $parentTopicEntity = null;

  // 2. THE INVERSE SIDE (Who are my children?)
  #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentTopicEntity', cascade: ['remove'])]
  private Collection $childrenTopicEntities;

  #[ORM\Column(type: 'string', nullable: true)]
  private ?string $restorePath = null;

  #[ORM\Column(type: 'datetime', nullable: true)]
  private ?\DateTimeInterface $deletedAt = null;

  public function __construct()
  {
    $this->cardEntities = new ArrayCollection();
    $this->childrenTopicEntities = new ArrayCollection();
  }

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
      $card->setTopicEntity($this);
    }

    return $this;
  }

  public function removeCard(CardEntity $card): self
  {
    if ($this->cardEntities->removeElement($card)) {
      // Set the owning side to null (unless already changed)
      if ($card->getTopicEntity() === $this) {
        $card->setTopicEntity(null);
      }
    }

    return $this;
  }

  /**
   * Get the value of parentTopicEntity
   *
   * @return ?self
   */
  public function getParentTopicEntity(): ?self
  {
    return $this->parentTopicEntity;
  }

  /**
   * Set the value of parentTopicEntity
   *
   * @param ?self $parentTopicEntity
   *
   * @return self
   */
  public function setParentTopicEntity(?self $parentTopicEntity): self
  {
    $this->parentTopicEntity = $parentTopicEntity;

    return $this;
  }

  /**
   * Get the value of childrenTopicEntities
   *
   * @return Collection
   */
  public function getChildrenTopicEntities(): Collection
  {
    return $this->childrenTopicEntities;
  }

  public function addChildTopicEntity(self $childTopicEntity): self
  {
    if (!$this->childrenTopicEntities->contains($childTopicEntity)) {
      $this->childrenTopicEntities->add($childTopicEntity);
      // Sync the relationship!
      $childTopicEntity->setParentTopicEntity($this);
    }

    return $this;
  }

  public function removeChildTopicEntity(self $childTopicEntity): self
  {
    if ($this->childrenTopicEntities->removeElement($childTopicEntity)) {
      // set the owning side to null (unless already changed)
      if ($childTopicEntity->getParentTopicEntity() === $this) {
        $childTopicEntity->setParentTopicEntity(null);
      }
    }

    return $this;
  }

  /**
   * Get the value of name
   *
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Set the value of name
   *
   * @param string $name
   *
   * @return self
   */
  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get the value of restorePath
   *
   * @return ?string
   */
  public function getRestorePath(): ?string
  {
    return $this->restorePath;
  }

  /**
   * Set the value of restorePath
   *
   * @param ?string $restorePath
   *
   * @return self
   */
  public function setRestorePath(?string $restorePath): self
  {
    $this->restorePath = $restorePath;

    return $this;
  }

  /**
   * Get the value of deletedAt
   *
   * @return ?\DateTimeInterface
   */
  public function getDeletedAt(): ?\DateTimeInterface
  {
    return $this->deletedAt;
  }
}
