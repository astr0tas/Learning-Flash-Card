<?php

namespace App\EventSubscriber;

use App\Config\Constants;
use App\Config\Routes;
use App\Utility\Utility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LocaleSubscriber implements EventSubscriberInterface
{
  public function __construct(
    private string $defaultLocale = Constants::DEFAULT_LOCALE,
    private string $currentLocale = Constants::DEFAULT_LOCALE,
    private string $redirectUrl = ""
  ) {}

  public static function getSubscribedEvents(): array
  {
    return [
      // Run this BEFORE the Router (priority 30)
      // so the router knows the correct locale to generate URLs with.
      KernelEvents::REQUEST => ['onKernelRequest', 30],
      // Priority -10 ensures this runs AFTER almost everything else,
      // just before the response leaves the server.
      KernelEvents::RESPONSE => ['onKernelResponse', -10],
    ];
  }

  public function onKernelRequest(RequestEvent $event): void
  {
    // Check if it's the main request (important for avoiding sub-requests)
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();

    // Check if this is a POST request to change the locale.
    if ($request->attributes->get('_route') === Routes::SET_LOCALE_ROUTE['NAME'] && $request->isMethod('POST')) {
      // Set the current locale from the route parameter
      $routeParams = $request->attributes->get('_route_params', []);
      $this->currentLocale = $routeParams[Routes::SET_LOCALE_ROUTE['ROUTE_PARAM']];
      // Set the target redirect url
      $this->redirectUrl = $request->headers->get('referer');
    } else {
      // For Google GSI redirects, check locale in the "state" parameter
      $state = $request->request->get('state', '');
      if (!empty($state)) {
        $stateParams = Utility::parseGSIState($state);
        if (isset($stateParams[Constants::PARAMETERS['locale']])) {
          $this->currentLocale = $stateParams[Constants::PARAMETERS['locale']];
        }
      }
      // Otherwise, use the cookie or default locale
      else {
        // Get the "locale" cookie from the request
        $cookieLocale = $request->cookies->get(Constants::COOKIES['locale']);

        // Check if the cookie exists
        if ($cookieLocale) {
          $this->currentLocale = $cookieLocale;
        } else {
          $this->currentLocale = $this->defaultLocale;
        }
      }
    }

    // Validate the locale (Security check)
    if (!in_array($this->currentLocale, array_values(Constants::LOCALES))) {
      $this->currentLocale = $this->defaultLocale;
    }

    // Set the request locale
    $request->setLocale($this->currentLocale);
  }

  public function onKernelResponse(ResponseEvent $event): void
  {

    $cookie = new Cookie(
      name: Constants::COOKIES['locale'],
      value: $this->currentLocale,
      expire: new \DateTime('+5 years'),
      path: '/',
      domain: null,
      secure: null, // Auto-detect
      httpOnly: false,
      sameSite: Cookie::SAMESITE_LAX
    );


    if (!empty($this->redirectUrl)) {
      $response = new RedirectResponse($this->redirectUrl);
      $response->headers->setCookie($cookie);
      $event->setResponse($response);
    } else {
      $response = $event->getResponse();
      $response->headers->setCookie($cookie);
    }
  }
}
