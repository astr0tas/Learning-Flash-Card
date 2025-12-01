<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;

class AuthenticationController extends AbstractController
{
  #[Route(path: Routes::LOGIN_ROUTE['URL'], name: Routes::LOGIN_ROUTE['NAME'])]
  public function LoginAction() {}

  #[Route(path: Routes::LOGOUT_ROUTE['URL'], name: Routes::LOGOUT_ROUTE['NAME'])]
  public function LogoutAction() {}

  #[Route(path: Routes::REGISTER_ROUTE['URL'], name: Routes::REGISTER_ROUTE['NAME'])]
  public function RegisterAction() {}

  #[Route(path: Routes::FORGOT_PASSWORD_ROUTE['URL'], name: Routes::FORGOT_PASSWORD_ROUTE['NAME'])]
  public function ForgotPasswordAction() {}

  #[Route(path: Routes::RESET_PASSWORD_ROUTE['URL'], name: Routes::RESET_PASSWORD_ROUTE['NAME'])]
  public function ResetPasswordAction() {}
}
