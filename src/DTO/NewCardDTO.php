<?php

namespace App\DTO;

class NewCardDTO extends BaseDTO
{
  private string $title;
  private ?string $subTitle = null;
  private ?string $description = null;
  private int $bag;

  /**
   * Get the value of bag
   *
   * @return int
   */
  public function getBag(): int
  {
    return $this->bag;
  }

  /**
   * Set the value of bag
   *
   * @param int $bag
   *
   * @return self
   */
  public function setBag(int $bag): self
  {
    $this->bag = $bag;

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
   * Get the value of subTitle
   *
   * @return string
   */
  public function getSubTitle(): ?string
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
  public function setSubTitle(?string $subTitle): self
  {
    $this->subTitle = $subTitle;

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
}
