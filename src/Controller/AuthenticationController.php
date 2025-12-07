<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationController extends BaseController
{
  #[Route(path: Routes::LOGIN_ROUTE['URL'], name: Routes::LOGIN_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function LoginAction(Request $request)
  {
    if ($request->isMethod(Request::METHOD_GET)) {
      // Display login form
      return $this->render('/views/authentication/login.html.twig');
    } else {
      // Handle login submission
    }
  }

  #[Route(path: Routes::LOGOUT_ROUTE['URL'], name: Routes::LOGOUT_ROUTE['NAME'], methods: ['POST'])]
  public function LogoutAction(Request $request) {}

  #[Route(path: Routes::REGISTER_ROUTE['URL'], name: Routes::REGISTER_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function RegisterAction(Request $request)
  {
    if ($request->isMethod(Request::METHOD_GET)) {
      // Display registration form
      return $this->render(view: '/views/authentication/register.html.twig');
    } else {
      // Handle registration submission
    }
  }

  #[Route(path: Routes::FORGOT_PASSWORD_ROUTE['URL'], name: Routes::FORGOT_PASSWORD_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function ForgotPasswordAction(Request $request)
  {
    if ($request->isMethod(Request::METHOD_GET)) {
      // Display forgot password form
      return $this->render(view: '/views/authentication/forgot_password.html.twig');
    } else {
      // Handle forgot password submission
    }
  }

  #[Route(path: Routes::RESET_PASSWORD_ROUTE['URL'], name: Routes::RESET_PASSWORD_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function ResetPasswordAction(Request $request)
  {
    if ($request->isMethod(Request::METHOD_GET)) {
      // Display reset password form
      return $this->render(view: '/views/authentication/reset_password.html.twig');
    } else {
      // Handle reset password submission
    }
  }

  #[Route(path: Routes::EMAIL_VERIFICATION_ROUTE['URL'], name: Routes::EMAIL_VERIFICATION_ROUTE['NAME'], methods: ['GET'])]
  public function EmailVerificationAction(Request $request) {}
}
