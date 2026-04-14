<?php

namespace App\DTO;

class NewBagDTO extends BaseDTO
{
  private string $newBagName;
  private string $newBagDescription;
  private ?int $parentCard = null;

  /**
   * Get the value of newBagName
   */
  public function getNewBagName()
  {
    return $this->newBagName;
  }

  /**
   * Set the value of newBagName
   */
  public function setNewBagName($newBagName): self
  {
    $this->newBagName = $newBagName;

    return $this;
  }

  /**
   * Get the value of newBagDescription
   */
  public function getNewBagDescription()
  {
    return $this->newBagDescription;
  }

  /**
   * Set the value of newBagDescription
   */
  public function setNewBagDescription($newBagDescription): self
  {
    $this->newBagDescription = $newBagDescription;

    return $this;
  }

  /**
   * Get the value of parentCard
   *
   * @return ?int
   */
  public function getParentCard(): ?int
  {
    return $this->parentCard;
  }

  /**
   * Set the value of parentCard
   *
   * @param ?int $parentCard
   *
   * @return self
   */
  public function setParentCard(?int $parentCard): self
  {
    $this->parentCard = $parentCard;

    return $this;
  }
}
