<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use Symfony\Component\HttpFoundation\Request;

class LocaleController extends BaseController
{
  #[Route(path: Routes::SET_LOCALE_ROUTE['URL'], name: Routes::SET_LOCALE_ROUTE['NAME'], methods: [Request::METHOD_GET])]
  public function ChangeLocale(string $locale, Request $request)
  {
    // This is just an empty function with a route to prevent 404 error
    // LocaleSubscriber will handle requests made to this route
  }
}
