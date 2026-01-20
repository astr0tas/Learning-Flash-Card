<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class BaseController extends AbstractController
{
  public TranslatorInterface $translator;
  public SessionInterface $session;
  public EntityManagerInterface $entityManager;

  #[Required]
  public function initProperties(TranslatorInterface $translator, RequestStack $requestStack, EntityManagerInterface $entityManager)
  {
    $this->translator = $translator;
    $this->session = $requestStack->getSession();
    $this->entityManager = $entityManager;
  }

  #[Route(path: Routes::SET_LOCALE_ROUTE['URL'], name: Routes::SET_LOCALE_ROUTE['NAME'], methods: ['POST'])]
  public function ChangeLocale(string $locale, Request $request)
  {
    // The LocaleSubscriber will handle setting the cookie
    // and updating the request locale.
    // Here, we just redirect back to the referer or home page.
    $referer = $request->headers->get('referer');
    $response = $this->redirect($referer ?? '/');

    return $response;
  }

  public function validate($input, $fields, bool $allowExtraFields = true): array
  {
    $validator = Validation::createValidator();
    $violations = $validator->validate($input, new Assert\Collection([
      'fields'           => $fields,
      'allowExtraFields' => $allowExtraFields,
    ]));

    $formattedViolations = [];
    if (count($violations) > 0) {
      foreach ($violations as $violation) {
        // 1. Get the field name (e.g., "username")
        $field = $violation->getPropertyPath();
        $field = str_replace(['[', ']'], '', $field); // Clean up the field name

        // 2. Get the message (e.g., "This value is too short.")
        $message = $violation->getMessage();

        // 3. Store it. Use an array [] in case one field has multiple errors
        $formattedViolations[$field][] = $message;
      }
    }

    return $formattedViolations;
  }
}
