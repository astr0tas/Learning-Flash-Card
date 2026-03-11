<?php

namespace App\Controller\User;

use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CardBagController extends BaseController
{
  #[Route(path: Routes::CARD_BAG_ROUTE_URL, name: Routes::CARD_BAG_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function index()
  {
    return $this->render(view: TwigTemplate::PAGE_USER_CARD_BAG);
  }
}
