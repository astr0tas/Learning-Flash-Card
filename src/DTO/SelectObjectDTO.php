<?php

namespace App\DTO;

class SelectObjectDTO extends BaseDTO
{
  private array $bag = [];
  private array $card = [];
  private ?int $newParentBag = null;

  /**
   * Get the value of bag
   *
   * @return array
   */
  public function getBag(): array
  {
    return $this->bag;
  }

  /**
   * Set the value of bag
   *
   * @param array $bag
   *
   * @return self
   */
  public function setBag(array $bag): self
  {
    $this->bag = $bag;

    return $this;
  }

  /**
   * Get the value of card
   *
   * @return array
   */
  public function getCard(): array
  {
    return $this->card;
  }

  /**
   * Set the value of card
   *
   * @param array $card
   *
   * @return self
   */
  public function setCard(array $card): self
  {
    $this->card = $card;

    return $this;
  }

  /**
   * Get the value of newParentBag
   *
   * @return ?int
   */
  public function getNewParentBag(): ?int
  {
    return $this->newParentBag;
  }

  /**
   * Set the value of newParentBag
   *
   * @param int|string|null $newParentBag
   *
   * @return self
   */
  public function setNewParentBag(int|string|null $newParentBag): self
  {
    if ($newParentBag === '') {
      $newParentBag = null;
    }

    $this->newParentBag = $newParentBag;

    return $this;
  }
}
