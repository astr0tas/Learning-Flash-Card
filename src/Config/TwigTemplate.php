<?php

namespace App\Config;

class TwigTemplate
{
  // View templates
  // Authentication templates
  public const PAGE_LOGIN = '/views/authentication/login.html.twig';
  public const PAGE_REGISTER = '/views/authentication/register.html.twig';
  public const PAGE_FORGOT_PASSWORD = '/views/authentication/forgot_password.html.twig';
  public const PAGE_RESET_PASSWORD = '/views/authentication/reset_password.html.twig';
  public const PAGE_VERIFY_EMAIL = '/views/authentication/verify_email.html.twig';

  // User templates
  public const PAGE_USER_HOME = '/views/user/index.html.twig';
  public const PAGE_USER_CARD_BAG = '/views/user/card_bag/index.html.twig';
  public const PAGE_USER_TRASH = '/views/user/trash/index.html.twig';

  // Admin templates
  public const PAGE_ADMIN_HOME = '/views/admin/index.html.twig';


  // Email templates
  public const EMAIL_RECOVERY_HTML = '/emails/recovery/index.html.twig';
  public const EMAIL_RECOVERY_TEXT = '/emails/recovery/text.txt.twig';

  public const EMAIL_VERIFICATION_HTML = '/emails/verify/index.html.twig';
  public const EMAIL_VERIFICATION_TEXT = '/emails/verify/text.txt.twig';
}
