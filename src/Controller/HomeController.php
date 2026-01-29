<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use App\Config\TwigTemplate;

class HomeController extends BaseController
{
  #[Route(path: Routes::HOME_ROUTE['URL'], name: Routes::HOME_ROUTE['NAME'], methods: ['GET'])]
  public function UserHome()
  {
    return $this->render(view: TwigTemplate::PAGES['user_home']);
  }

  #[Route(path: Routes::ADMIN_HOME_ROUTE['URL'], name: Routes::ADMIN_HOME_ROUTE['NAME'], methods: ['GET'])]
  public function AdminHome()
  {
    return $this->render(view: TwigTemplate::PAGES['admin_home']);
  }
}
