<?php

namespace App\DTO;

class LoginDTO
{
  public string $email;
  public string $password;
  public string $rememberMe;
}

class LoginWithGoogleDTO
{
  public string $state;
  public string $code;
}

class ForgotPasswordDTO
{
  public string $email;
}
