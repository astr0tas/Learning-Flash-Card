<?php

namespace App\Config;

class TwigTemplate
{
  public const PAGES = [
    # Authentication pages
    'login' => '/views/authentication/login.html.twig',
    'register' => '/views/authentication/register.html.twig',
    'forgot_password' => '/views/authentication/forgot_password.html.twig',
    'recovery_email_sent' => '/views/authentication/recovery_email_sent.html.twig',
    'reset_password' => '/views/authentication/reset_password.html.twig',
    # User pages
    'user_home' => '/views/user/index.html.twig',
    # Admin pages
    'admin_home' => '/views/admin/index.html.twig',
  ];
  public const EMAILS = [
    'recovery' => [
      'html' => '/emails/recovery/index.html.twig',
      'text' => '/emails/recovery/text.txt.twig'
    ]
  ];
}
