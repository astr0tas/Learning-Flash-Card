<?php

namespace App\DTO;

class ResetPasswordDTO extends BaseDTO
{
  private string $email = '';
  private string $token = '';
  private string $newPassword = '';
  private string $confirmNewPassword = '';

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

  /**
   * Get the value of newPassword
   *
   * @return string
   */
  public function getNewPassword(): string
  {
    return $this->newPassword;
  }

  /**
   * Set the value of newPassword
   *
   * @param string $newPassword
   *
   * @return self
   */
  public function setNewPassword(string $newPassword): self
  {
    $this->newPassword = $newPassword;

    return $this;
  }

  /**
   * Get the value of confirmNewPassword
   *
   * @return string
   */
  public function getConfirmNewPassword(): string
  {
    return $this->confirmNewPassword;
  }

  /**
   * Set the value of confirmNewPassword
   *
   * @param string $confirmNewPassword
   *
   * @return self
   */
  public function setConfirmNewPassword(string $confirmNewPassword): self
  {
    $this->confirmNewPassword = $confirmNewPassword;

    return $this;
  }
}
