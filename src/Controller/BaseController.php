<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use Symfony\Component\HttpFoundation\Request;
use App\Config\Constants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
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
  public function setTranslator(TranslatorInterface $translator): void
  {
    $this->translator = $translator;
  }

  #[Required]
  public function setSession(RequestStack $requestStack): void
  {
    $this->session = $requestStack->getSession();
  }

  #[Required]
  public function setEntityManager(EntityManagerInterface $entityManager): void
  {
    $this->entityManager = $entityManager;
  }

  #[Route(path: Routes::SET_LOCALE_ROUTE['URL'], name: Routes::SET_LOCALE_ROUTE['NAME'], methods: ['GET'])]
  public function ChangeLocale(string $locale, Request $request)
  {
    // 1. Store the locale in the cookie (or session)
    // Create a new Cookie instance
    $cookie = Cookie::create(
      Constants::COOKIES['locale'],  // Name of the cookie
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
