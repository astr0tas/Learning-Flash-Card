<?php

namespace App\DTO;

class LoginWithGoogleDTO extends BaseDTO
{
  private string $state = '';
  private string $code = '';

  /**
   * Get the value of code
   *
   * @return string
   */
  public function getCode(): string
  {
    return $this->code;
  }

  /**
   * Set the value of code
   *
   * @param string $code
   *
   * @return self
   */
  public function setCode(string $code): self
  {
    $this->code = $code;

    return $this;
  }

  /**
   * Get the value of state
   *
   * @return string
   */
  public function getState(): string
  {
    return $this->state;
  }

  /**
   * Set the value of state
   *
   * @param string $state
   *
   * @return self
   */
  public function setState(string $state): self
  {
    $this->state = $state;

    return $this;
  }
}
