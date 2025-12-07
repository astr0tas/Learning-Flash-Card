<?php

namespace App\Config;

class Routes
{
  // Authentication Routes
  public const LOGIN_ROUTE = [
    'URL' => '/login',
    'NAME' => 'login',
  ];
  public const LOGOUT_ROUTE = [
    'URL' => '/logout',
    'NAME' => 'logout',
  ];
  public const REGISTER_ROUTE = [
    'URL' => '/register',
    'NAME' => 'register',
  ];
  public const FORGOT_PASSWORD_ROUTE = [
    'URL' => '/forgot-password',
    'NAME' => 'forgot_password',
  ];
  public const RESET_PASSWORD_ROUTE = [
    'URL' => '/reset-password',
    'NAME' => 'reset_password',
  ];

  public const EMAIL_VERIFICATION_ROUTE = [
    'URL' => '/verify-email',
    'NAME' => 'verify_email',
  ];

  public const SET_LOCALE_ROUTE = [
    'URL' => '/set-locale/{locale}',
    'NAME' => 'set_locale',
  ];

  public const HOME_ROUTE = [
    'URL' => '/',
    'NAME' => 'home',
  ];
}
