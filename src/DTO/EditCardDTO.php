<?php

namespace App\DTO;

use App\Config\Constants;

class EditCardDTO extends BaseDTO
{
  private string $title;
  private ?string $subtitle = null;
  private ?string $description = null;
  private ?int $card = null;
  private string $cardType;
  private string $cardColor = Constants::FLASH_CARD_DEFAULT_COLOR;
  private string $cardTextColor = Constants::FLASH_CARD_DEFAULT_TEXT_COLOR;

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
   * Get the value of subtitle
   *
   * @return ?string
   */
  public function getSubtitle(): ?string
  {
    return $this->subtitle;
  }

  /**
   * Set the value of subtitle
   *
   * @param ?string $subtitle
   *
   * @return self
   */
  public function setSubtitle(?string $subtitle): self
  {
    $this->subtitle = $subtitle;

    return $this;
  }

  /**
   * Get the value of description
   *
   * @return ?string
   */
  public function getDescription(): ?string
  {
    return $this->description;
  }

  /**
   * Set the value of description
   *
   * @param ?string $description
   *
   * @return self
   */
  public function setDescription(?string $description): self
  {
    $this->description = $description;

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
   * Get the value of card
   *
   * @return ?int
   */
  public function getCard(): ?int
  {
    return $this->card;
  }

  /**
   * Set the value of card
   *
   * @param ?int $card
   *
   * @return self
   */
  public function setCard(?int $card): self
  {
    $this->card = $card;

    return $this;
  }
}
