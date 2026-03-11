<?php

namespace App\Controller\Admin;

use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class AdminHomeController extends BaseController
{
  #[Route(path: Routes::ADMIN_HOME_ROUTE_URL, name: Routes::ADMIN_HOME_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function AdminHome()
  {
    return $this->render(view: TwigTemplate::PAGE_ADMIN_HOME);
  }
}
