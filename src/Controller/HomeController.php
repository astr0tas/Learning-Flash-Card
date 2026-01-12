<?php

namespace App\Controller;

use App\Config\Constants;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;

class HomeController extends BaseController
{
  #[Route(path: Routes::HOME_ROUTE['URL'], name: Routes::HOME_ROUTE['NAME'], methods: ['GET'])]
  public function UserHome()
  {
    return $this->render('/views/user/index.html.twig');
  }

  #[Route(path: Routes::ADMIN_HOME_ROUTE['URL'], name: Routes::ADMIN_HOME_ROUTE['NAME'], methods: ['GET'])]
  public function AdminHome()
  {
    return $this->render('/views/admin/index.html.twig');
  }
}
