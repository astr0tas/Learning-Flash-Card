<?php

namespace App\DTO;

class TokenDTO extends BaseDTO
{
  private string $token = '';
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

  /**
   * Get the value of token
   *
   * @return string
   */
  public function getToken(): string
  {
    return $this->token;
  }

  /**
   * Set the value of token
   *
   * @param string $token
   *
   * @return self
   */
  public function setToken(string $token): self
  {
    $this->token = $token;

    return $this;
  }
}
