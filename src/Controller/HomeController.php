<?php

namespace App\Controller;

use App\Config\Constants;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;

class HomeController extends BaseController
{
  #[Route(path: Routes::HOME_ROUTE['URL'], name: Routes::HOME_ROUTE['NAME'], methods: ['GET'])]
  public function index()
  {
    $user = $this->getUser();

    if ($this->isGranted(Constants::ROLES['admin'], $user)) {
      return $this->renderAdminHome();
    }

    return $this->renderUserHome();
  }

  private function renderAdminHome()
  {
    return $this->render('/views/admin/index.html.twig');
  }

  private function renderUserHome()
  {
    return $this->render('/views/user/index.html.twig');
  }
}
