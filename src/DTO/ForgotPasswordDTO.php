<?php

namespace App\DTO;

class ForgotPasswordDTO extends BaseDTO
{
  private string $email = '';

  /**
   * Get the value of email
   *
   * @return string
   */
  public function getEmail(): string
  {
    return $this->email;
  }

  /**
   * Set the value of email
   *
   * @param string $email
   *
   * @return self
   */
  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }
}
