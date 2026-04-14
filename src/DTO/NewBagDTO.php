<?php

namespace App\DTO;

class NewBagDTO extends BaseDTO
{
  private string $newBagName;
  private string $newBagDescription;
  private ?int $parentBag = null;

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
   * Get the value of parentBag
   *
   * @return ?int
   */
  public function getParentBag(): ?int
  {
    return $this->parentBag;
  }

  /**
   * Set the value of parentBag
   *
   * @param ?int $parentBag
   *
   * @return self
   */
  public function setParentBag(?int $parentBag): self
  {
    $this->parentBag = $parentBag;

    return $this;
  }
}
