<?php

namespace App\Entity;

use App\Config\Constants;
use App\Config\Constraints;
use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ORM\Table(name: Constants::TABLE_CARD)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class CardEntity extends BaseEntity
{
  #[ORM\Column(type: 'string', length: Constraints::CARD_TITLE_MAX_LENGTH)]
  private string $title;

  #[ORM\Column(type: 'string', length: Constraints::CARD_SUBTITLE_MAX_LENGTH, nullable: true)]
  private ?string $subtitle;

  #[ORM\Column(type: 'string', length: Constraints::CARD_DESCRIPTION_MAX_LENGTH, nullable: true)]
  private ?string $description;

  #[ORM\Column(type: 'string', length: 10, options: ['default' => Constants::FLASH_CARD_DEFAULT_TYPE, 'comment' => 'Possible values: ' . Constants::FLASH_CARD_BAG_TYPES_STR])]
  private string $cardType = Constants::FLASH_CARD_DEFAULT_TYPE;

  #[ORM\Column(type: 'string', length: 10, options: ['default' => Constants::FLASH_CARD_DEFAULT_COLOR])]
  private string $cardColor = Constants::FLASH_CARD_DEFAULT_COLOR;

  #[ORM\Column(type: 'string', length: 10, options: ['default' => Constants::FLASH_CARD_DEFAULT_TEXT_COLOR])]
  private string $cardTextColor = Constants::FLASH_CARD_DEFAULT_TEXT_COLOR;

  #[ORM\ManyToOne(targetEntity: CardBagEntity::class, inversedBy: 'cardEntities')]
  #[ORM\JoinColumn(name: 'card_bag_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
  private ?CardBagEntity $cardBagEntity = null;

  #[ORM\ManyToOne(targetEntity: UserEntity::class, inversedBy: 'cardEntities')]
  #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  private ?UserEntity $userEntity = null;

  #[ORM\Column(type: 'string', nullable: true)]
  private ?string $restorePath = null;

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
   * Get the value of subtitle
   *
   * @return string
   */
  public function getSubtitle(): string
  {
    return $this->subtitle;
  }

  /**
   * Set the value of subtitle
   *
   * @param string $subtitle
   *
   * @return self
   */
  public function setSubtitle(string $subtitle): self
  {
    $this->subtitle = $subtitle;

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

  /**
   * Get the value of cardColor
   *
   * @return string
   */
  public function getCardColor(): string
  {
    return $this->cardColor;
  }

  /**
   * Set the value of cardColor
   *
   * @param string $cardColor
   *
   * @return self
   */
  public function setCardColor(string $cardColor): self
  {
    $this->cardColor = $cardColor;

    return $this;
  }

  /**
   * Get the value of cardTextColor
   *
   * @return string
   */
  public function getCardTextColor(): string
  {
    return $this->cardTextColor;
  }

  /**
   * Set the value of cardTextColor
   *
   * @param string $cardTextColor
   *
   * @return self
   */
  public function setCardTextColor(string $cardTextColor): self
  {
    $this->cardTextColor = $cardTextColor;

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
