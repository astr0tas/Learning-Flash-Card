<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use Symfony\Component\HttpFoundation\Request;
use App\Config\Constants;
use Symfony\Component\HttpFoundation\Cookie;

class BaseController extends AbstractController
{
  protected int $userID;

  #[Route(path: Routes::SET_LOCALE_ROUTE['URL'], name: Routes::SET_LOCALE_ROUTE['NAME'], methods: ['GET'])]
  public function ChangeLocale(string $locale, Request $request)
  {
    // 1. Store the locale in the cookie (or session)
    // Create a new Cookie instance
    $cookie = Cookie::create(
      Constants::LOCALE_COOKIE_NAME,  // Name of the cookie
      $locale, // Value of the cookie
      time() + (3600 * 24 * 365 * 100), // Expiration time (e.g., 100 years from now)
      '/',               // Path (e.g., available for the entire domain)
      domain: '',    // Domain (e.g., for example.com and its subdomains)
      secure: false,              // Secure (send only over HTTPS)
      httpOnly: true,              // HttpOnly (not accessible via JavaScript)
      raw: true,             // Raw (value is not URL-encoded)
      sameSite: 'lax'              // SameSite attribute (e.g., 'lax', 'strict', 'none')
    );

    // 2. Redirect back to where the user came from with the cookie set
    $referer = $request->headers->get(key: 'referer');
    $response = $this->redirect($referer ?? '/');
    $response->headers->setCookie($cookie);
    return $response;
  }
}
