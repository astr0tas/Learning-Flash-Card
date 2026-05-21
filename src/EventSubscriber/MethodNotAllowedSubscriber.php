<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class MethodNotAllowedSubscriber implements EventSubscriberInterface
{
  public function __construct(private TranslatorInterface $translator) {}

  public static function getSubscribedEvents(): array
  {
    // We listen to the EXCEPTION event with high priority
    return [
      KernelEvents::EXCEPTION => ['onKernelException', 200],
    ];
  }

  public function onKernelException(ExceptionEvent $event): void
  {
    $exception = $event->getThrowable();

    // 1. Check if the error is "405 Method Not Allowed"
    if (!$exception instanceof MethodNotAllowedHttpException) {
      return;
    }

    // 2. Throw a "404 Not Found" instead
    // This stops the current 405 process and triggers Symfony's 404 handling
    throw new NotFoundHttpException($this->translator->trans('navigation_error.error_404'), $exception);
  }
}
