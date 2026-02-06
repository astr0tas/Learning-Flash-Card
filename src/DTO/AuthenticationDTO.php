<?php

namespace App\DTO;

class LoginDTO extends BaseDTO
{
  public string $email = '';
  public string $password = '';
  public string $rememberMe = '';
}

class LoginWithGoogleDTO extends BaseDTO
{
  public string $state = '';
  public string $code = '';
}

class ForgotPasswordDTO extends BaseDTO
{
  public string $email = '';
}

class RegisterDTO extends BaseDTO
{
  public string $firstName = '';
  public string $middleName = '';
  public string $lastName = '';
  public string $email = '';
  public string $password = '';
  public string $confirmPassword = '';
}
