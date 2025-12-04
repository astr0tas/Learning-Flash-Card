<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;

class HomeController extends BaseController
{
  #[Route(path: Routes::HOME_ROUTE['URL'], name: Routes::HOME_ROUTE['NAME'], methods: ['GET'])]
  public function index() {}
}
