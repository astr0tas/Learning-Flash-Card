<?php

namespace App\DTO;

class NewBagDTO extends BaseDTO
{
  private $newBagName;
  private $newBagDescription;
  private $newBagType;

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
   * Get the value of newBagType
   */
  public function getNewBagType()
  {
    return $this->newBagType;
  }

  /**
   * Set the value of newBagType
   */
  public function setNewBagType($newBagType): self
  {
    $this->newBagType = $newBagType;

    return $this;
  }
}
