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
