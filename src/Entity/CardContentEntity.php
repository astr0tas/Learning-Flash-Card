<?php

namespace App\Entity;

use App\Config\Constants;
use App\Repository\CardContentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardContentRepository::class)]
#[ORM\Table(name: Constants::TABLE_CARD_CONTENT)]
class CardContentEntity extends BaseEntity
{
  #[ORM\ManyToOne(targetEntity: CardEntity::class)]
  #[ORM\JoinColumn(name: 'card_id', nullable: false)]
  private ?CardEntity $cardEntity = null;

  /**
   * Get the value of cardEntity
   *
   * @return ?CardEntity
   */
  public function getCardEntity(): ?CardEntity
  {
    return $this->cardEntity;
  }

  /**
   * Set the value of cardEntity
   *
   * @param ?CardEntity $cardEntity
   *
   * @return self
   */
  public function setCardEntity(?CardEntity $cardEntity): self
  {
    $this->cardEntity = $cardEntity;

    return $this;
  }
}
