<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Config\Constants;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends AbstractExtension
{
  public function __construct(
    private RequestStack $requestStack
  ) {}

  // public function getFilters(): array
  // {
  //     return [
  //         // If your filter generates SAFE HTML, you should add a third
  //         // parameter: ['is_safe' => ['html']]
  //         // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
  //         new TwigFilter('filter_name', [AppExtensionRuntime::class, 'doSomething']),
  //     ];
  // }

  public function getFunctions(): array
  {
    return [
      new TwigFunction('get_locale', [$this, 'getLocaleFromCookie']),
      new TwigFunction('is_dark_mode', [$this, 'isDarkMode']),
    ];
  }

  public function getLocaleFromCookie(): string
  {
    if (empty($_COOKIE[Constants::COOKIE_LOCALE])) {
      return Constants::DEFAULT_LOCALE;
    }

    return \strval($_COOKIE[Constants::COOKIE_LOCALE]) ?? Constants::DEFAULT_LOCALE;
  }

  public function isDarkMode(): bool
  {
    // 1. Safely get the current request
    $request = $this->requestStack->getCurrentRequest();

    if (!$request) {
      return false; // Failsafe for CLI commands or missing requests
    }

    // 2. Extract the cookie (returns null if it doesn't exist)
    $cookieValue = $request->cookies->get('dark_mode');

    // 3. Securely convert the string to a boolean
    // This perfectly translates strings like "true", "1", "on", or "yes" into true,
    // and everything else (or null) into false.
    return filter_var($cookieValue, FILTER_VALIDATE_BOOLEAN);
  }
}
