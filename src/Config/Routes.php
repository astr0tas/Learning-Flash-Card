<?php

namespace App\Config;

class Routes
{
  // Authentication Routes
  public const LOGIN_ROUTE_URL = '/login';
  public const LOGIN_ROUTE_NAME = 'login';

  public const LOGIN_SUBMIT_ROUTE_URL = '/login';
  public const LOGIN_SUBMIT_ROUTE_NAME = 'login_submit';

  public const LOGIN_WITH_GOOGLE_ROUTE_URL = '/login/google';
  public const LOGIN_WITH_GOOGLE_ROUTE_NAME = 'login_with_google';

  public const LOGOUT_ROUTE_URL = '/logout';
  public const LOGOUT_ROUTE_NAME = 'logout';

  public const REGISTER_ROUTE_URL = '/register';
  public const REGISTER_ROUTE_NAME = 'register';

  public const REGISTER_SUBMIT_ROUTE_URL = '/register';
  public const REGISTER_SUBMIT_ROUTE_NAME = 'register_submit';

  public const FORGOT_PASSWORD_ROUTE_URL = '/forgot-password';
  public const FORGOT_PASSWORD_ROUTE_NAME = 'forgot_password';

  public const FORGOT_PASSWORD_SUBMIT_ROUTE_URL = '/forgot-password';
  public const FORGOT_PASSWORD_SUBMIT_ROUTE_NAME = 'forgot_password_submit';

  public const RESET_PASSWORD_ROUTE_URL = '/reset-password';
  public const RESET_PASSWORD_ROUTE_NAME = 'reset_password';

  public const RESET_PASSWORD_SUBMIT_ROUTE_URL = '/reset-password';
  public const RESET_PASSWORD_SUBMIT_ROUTE_NAME = 'reset_password_submit';

  public const EMAIL_VERIFICATION_ROUTE_URL = '/verify-email';
  public const EMAIL_VERIFICATION_ROUTE_NAME = 'verify_email';

  // Locale Route
  public const SET_LOCALE_ROUTE_URL = '/set-locale/{locale}';
  public const SET_LOCALE_ROUTE_NAME = 'set_locale';
  public const SET_LOCALE_ROUTE_PARAM = 'locale';

  // User Routes
  public const HOME_ROUTE_URL = '/';
  public const HOME_ROUTE_NAME = 'home';
  public const CARD_BAG_ROUTE_URL = '/card-bag';
  public const CARD_BAG_ROUTE_NAME = 'card-bag';
  public const TRASH_ROUTE_URL = '/trash';
  public const TRASH_ROUTE_NAME = 'trash';
  public const ACCOUNT_ROUTE_URL = '/account';
  public const ACCOUNT_ROUTE_NAME = 'account';

  // Admin Routes
  public const ADMIN_HOME_ROUTE_URL = '/admin';
  public const ADMIN_HOME_ROUTE_NAME = 'admin_home';
}
