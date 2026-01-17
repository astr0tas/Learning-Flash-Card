<?php

namespace App\EventSubscriber;

use App\Config\Routes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfValidationSubscriber implements EventSubscriberInterface
{
  private const EXCEPTION_ROUTES = [
    // Add route names here that should be excluded from CSRF validation
    // e.g., 'some_route_name',
    Routes::LOGIN_WITH_GOOGLE_ROUTE['NAME']
  ];

  public function __construct(
    private CsrfTokenManagerInterface $csrfTokenManager
  ) {}

  public function onKernelController(ControllerEvent $event): void
  {
    // 1. Check if this request is the main request
    if (!$event->isMainRequest()) {
      return;
    }

    // 2. Only check on POST methods
    $request = $event->getRequest();
    if (!$request->isMethod('POST')) {
      return;
    }

    // 3. Check for exception routes
    $routeName = $request->attributes->get('_route');

    if (in_array($routeName, self::EXCEPTION_ROUTES)) {
      return;
    }

    // 4. Perform the Validation
    // 'csrf_token' is the form field name you are sending
    $tokenValue = $request->request->get('csrf_token');

    // 'csrf_token' is the ID/Key you used to generate it (e.g. {{ csrf_token('authenticate') }})
    // Adjust this ID if you use different IDs for different forms
    $tokenId = 'csrf_token';

    if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($tokenId, $tokenValue))) {
      throw new AccessDeniedHttpException('Invalid CSRF token.');
    }

    // 5. If valid, remove csrf_token from request data to prevent form validation issues
    $request->request->remove('csrf_token');
  }

  public static function getSubscribedEvents(): array
  {
    return [
      KernelEvents::CONTROLLER => ['onKernelController', 31],
    ];
  }
}
