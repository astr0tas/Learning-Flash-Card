<?php

namespace App\DTO;

class LoginDTO extends BaseDTO
{
  private string $email = '';
  private string $password = '';
  private string $rememberMe = '';

  /**
   * Get the value of rememberMe
   *
   * @return string
   */
  public function getRememberMe(): string
  {
    return $this->rememberMe;
  }

  /**
   * Set the value of rememberMe
   *
   * @param string $rememberMe
   *
   * @return self
   */
  public function setRememberMe(string $rememberMe): self
  {
    $this->rememberMe = $rememberMe;

    return $this;
  }

  /**
   * Get the value of password
   *
   * @return string
   */
  public function getPassword(): string
  {
    return $this->password;
  }

  /**
   * Set the value of password
   *
   * @param string $password
   *
   * @return self
   */
  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

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
