<?php

namespace App\Config;

class Constants
{
  public const APP_ENV = [
    'development' => 'dev',
    'production'  => 'pro',
  ];
  public const ROLES = [
    'user' => 'ROLE_USER',
    'admin' => 'ROLE_ADMIN',
    'public' => 'PUBLIC_ACCESS',
  ];
  public const COOKIES = [
    'locale'    => 'locale',
  ];
  public const PARAMETERS = [
    'locale'    => 'locale',
  ];
  public const LOCALES = [
    'vi' => 'vi',
    'en' => 'en',
  ];
  public const DEFAULT_LOCALE = self::LOCALES['vi'];
  public const BCRYPT_COST = 12;
  public const GOOGLE_OAUTH_PASSWORD = 'google_oauth_password'; // Do not touch this!!!
  public const REQUEST_LIMIT = 5;
  public const SESSION = [];
  public const LOG_CHANNELS = [
    'email_content'  => 'email_content.logger',
    'email_service'  => 'email_service.logger',
  ];
  public const EMAIL_SUBJECTS = [
    'recovery' => 'Khôi phục mật khẩu | Account Recovery',
  ];
  public const TABLES = [
    'user' => 'user_tbl',
    'recovery_token' => 'recovery_token_tbl',
  ];
}
