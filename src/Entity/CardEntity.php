<?php

namespace App\Entity;

use App\Config\Constants;
use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\Table(name: Constants::TABLE_CARD)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class CardEntity extends BaseEntity
{
  #[ORM\Column(type: 'string', length: 255)]
  private string $title;

  #[ORM\Column(type: 'string', length: 255, nullable: true)]
  private ?string $subTitle;

  #[ORM\Column(type: 'string', length: 1000, nullable: true)]
  private ?string $description;

  #[ORM\Column(type: 'string', length: 10, options: ['default' => Constants::FLASH_CARD_DEFAULT_TYPE, 'comment' => 'Possible values: ' . Constants::FLASH_CARD_BAG_TYPES_STR])]
  private string $cardType;

  #[ORM\ManyToOne(targetEntity: CardBagEntity::class)]
  #[ORM\JoinColumn(name: 'card_bag_id', nullable: true)]
  private ?CardBagEntity $cardBagEntity = null;

  #[ORM\ManyToOne(targetEntity: UserEntity::class)]
  #[ORM\JoinColumn(name: 'user_id', nullable: false)]
  private ?UserEntity $userEntity = null;

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
   * Get the value of cardBagEntity
   *
   * @return ?CardBagEntity
   */
  public function getCardBagEntity(): ?CardBagEntity
  {
    return $this->cardBagEntity;
  }

  /**
   * Set the value of cardBagEntity
   *
   * @param ?CardBagEntity $cardBagEntity
   *
   * @return self
   */
  public function setCardBagEntity(?CardBagEntity $cardBagEntity): self
  {
    $this->cardBagEntity = $cardBagEntity;

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
   * Get the value of subTitle
   *
   * @return string
   */
  public function getSubTitle(): string
  {
    return $this->subTitle;
  }

  /**
   * Set the value of subTitle
   *
   * @param string $subTitle
   *
   * @return self
   */
  public function setSubTitle(string $subTitle): self
  {
    $this->subTitle = $subTitle;

    return $this;
  }

  /**
   * Get the value of cardType
   *
   * @return string
   */
  public function getCardType(): string
  {
    return $this->cardType;
  }

  /**
   * Set the value of cardType
   *
   * @param string $cardType
   *
   * @return self
   */
  public function setCardType(string $cardType): self
  {
    $this->cardType = $cardType;

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
}
