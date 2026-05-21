<?php

namespace App\DTO;

class RegisterDTO extends BaseDTO
{
  private string $firstName = '';
  private string $middleName = '';
  private string $lastName = '';
  private string $email = '';
  private string $password = '';
  private string $confirmPassword = '';

  /**
   * Get the value of confirmPassword
   *
   * @return string
   */
  public function getConfirmPassword(): string
  {
    return $this->confirmPassword;
  }

  /**
   * Set the value of confirmPassword
   *
   * @param string $confirmPassword
   *
   * @return self
   */
  public function setConfirmPassword(string $confirmPassword): self
  {
    $this->confirmPassword = $confirmPassword;

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

  /**
   * Get the value of lastName
   *
   * @return string
   */
  public function getLastName(): string
  {
    return $this->lastName;
  }

  /**
   * Set the value of lastName
   *
   * @param string $lastName
   *
   * @return self
   */
  public function setLastName(string $lastName): self
  {
    $this->lastName = $lastName;

    return $this;
  }

  /**
   * Get the value of middleName
   *
   * @return string
   */
  public function getMiddleName(): string
  {
    return $this->middleName;
  }

  /**
   * Set the value of middleName
   *
   * @param string $middleName
   *
   * @return self
   */
  public function setMiddleName(string $middleName): self
  {
    $this->middleName = $middleName;

    return $this;
  }

  /**
   * Get the value of firstName
   *
   * @return string
   */
  public function getFirstName(): string
  {
    return $this->firstName;
  }

  /**
   * Set the value of firstName
   *
   * @param string $firstName
   *
   * @return self
   */
  public function setFirstName(string $firstName): self
  {
    $this->firstName = $firstName;

    return $this;
  }
}
