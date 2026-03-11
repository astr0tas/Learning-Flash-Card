<?php

namespace App\Controller\User;

use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class TrashController extends BaseController
{
  #[Route(path: Routes::TRASH_ROUTE_URL, name: Routes::TRASH_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function index()
  {
    return $this->render(view: TwigTemplate::PAGE_USER_TRASH);
  }
}
