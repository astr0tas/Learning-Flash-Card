<?php

namespace App\Config;

class Constants
{
  public const ROLES = [
    'user' => 'ROLE_USER',
    'admin' => 'ROLE_ADMIN',
    'public' => 'PUBLIC_ACCESS',
  ];
  public const COOKIES = [
    'locale'    => 'locale',
  ];
  public const LOCALES = [
    'vi' => 'vi',
    'en' => 'en',
  ];
  public const DEFAULT_LOCALE = self::LOCALES['vi'];
  public const BCRYPT_COST = 12;
  public const GOOGLE_OAUTH_PASSWORD = 'google_oauth_password'; // Do not touch this!!!
  public const SESSION = [
    'recovery_email_sent_flash_guard' => 'recovery_email_sent_flash_guard'
  ];
}
