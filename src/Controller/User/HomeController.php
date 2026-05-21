<?php

namespace App\Controller\User;

use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController
{
  #[Route(path: Routes::HOME_ROUTE_URL, name: Routes::HOME_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function UserHome()
  {
    return $this->render(view: TwigTemplate::PAGE_USER_HOME);
  }
}
