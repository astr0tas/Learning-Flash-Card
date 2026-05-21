<?php

namespace App\DTO;

class BagNavigationTreeDTO extends BaseDTO
{
  private int $bagId;
  private string $bagName;
  private ?BagNavigationTreeDTO $child = null;

  /**
   * Get the value of child
   *
   * @return ?BagNavigationTreeDTO
   */
  public function getChild(): ?BagNavigationTreeDTO
  {
    return $this->child;
  }

  /**
   * Set the value of child
   *
   * @param ?BagNavigationTreeDTO $child
   *
   * @return self
   */
  public function setChild(?BagNavigationTreeDTO $child): self
  {
    $this->child = $child;

    return $this;
  }

  /**
   * Get the value of bagName
   *
   * @return string
   */
  public function getBagName(): string
  {
    return $this->bagName;
  }

  /**
   * Set the value of bagName
   *
   * @param string $bagName
   *
   * @return self
   */
  public function setBagName(string $bagName): self
  {
    $this->bagName = $bagName;

    return $this;
  }

  /**
   * Get the value of bagId
   *
   * @return int
   */
  public function getBagId(): int
  {
    return $this->bagId;
  }

  /**
   * Set the value of bagId
   *
   * @param int $bagId
   *
   * @return self
   */
  public function setBagId(int $bagId): self
  {
    $this->bagId = $bagId;

    return $this;
  }
}
