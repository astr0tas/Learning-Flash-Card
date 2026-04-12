<?php

namespace App\DTO;

class NewBagDTO extends BaseDTO
{
  private $newBagName;

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
}
