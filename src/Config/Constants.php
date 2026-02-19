<?php

namespace App\Config;

class Constants
{
  // Application settings (do not modify)
  public const APP_ENV_DEVELOPMENT = 'dev';
  public const APP_ENV_PRODUCTION = 'prod';
  public const AUTHENTICATOR_NAME = 'form_login';
  public const BCRYPT_COST = 12;
  public const REQUEST_LIMIT = 5;

  // User roles
  public const ROLE_USER = 'ROLE_USER';
  public const ROLE_ADMIN = 'ROLE_ADMIN';
  public const ROLE_PUBLIC = 'PUBLIC_ACCESS';

  // Cookies
  public const COOKIE_LOCALE = 'locale';

  // Parameters
  public const PARAMETER_CSRF_TOKEN = 'csrf_token';

  // Locales
  public const LOCALE_VI = 'vi';
  public const LOCALE_EN = 'en';
  public const DEFAULT_LOCALE = self::LOCALE_VI;
  public const LOCALES = [
    self::LOCALE_VI => self::LOCALE_VI,
    self::LOCALE_EN => self::LOCALE_EN
  ];

  // Session keys
  public const SESSION_OAUTH2STATE = 'oauth2state';

  // Email subjects
  public const EMAIL_SUBJECT_PASSWORD_RECOVERY = 'Khôi phục mật khẩu | Password Recovery';
  public const EMAIL_SUBJECT_EMAIL_VERIFICATION = 'Xác thực tài khoản | Verify Account';

  // Entity table names
  public const TABLE_USER = 'user_tbl';
  public const TABLE_RECOVERY_TOKEN = 'recovery_token_tbl';
  public const TABLE_EMAIL_VERIFICATION_TOKEN = 'email_verification_token_tbl';

  // Technical messages
  public const MESSAGE_INVALID_CSRF = 'Invalid CSRF token.';
  public const UNKNOWN_LOG_LEVEL = 'Unknown log level.';
}
