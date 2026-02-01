<?php

namespace App\Controller;

use App\Config\Constants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
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
  public Response $unprocessableEntityResponse;

  #[Required]
  public function initProperties(TranslatorInterface $translator, RequestStack $requestStack, EntityManagerInterface $entityManager)
  {
    $this->translator = $translator;
    $this->session = $requestStack->getSession();
    $this->entityManager = $entityManager;
    $this->unprocessableEntityResponse = new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY);
  }

  #[Route(path: Routes::SET_LOCALE_ROUTE['URL'], name: Routes::SET_LOCALE_ROUTE['NAME'], methods: [Request::METHOD_GET])]
  public function ChangeLocale(string $locale, Request $request)
  {
    // This is just an empty function with a route to prevent 404 error
    // LocaleSubscriber will handle requests made to this route
  }

  /**
   * Validate input data against specified constraints
   * @param mixed $input The input data to validate
   * @param array $fields An associative array of field names to their constraints
   * @param bool $allowExtraFields Whether to allow extra fields not specified in $fields
   * @return array An array of validation errors, empty if none found
   */
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

  /**
   * Check if the user sent too many requests for tokens (e.g., recovery tokens, verification tokens, etc.)
   * @param string $entityClass The entity class to check (e.g., UserEntity::class)
   * @param string $alias The alias to use in the query (default: 't')
   * @param array $conditions Additional conditions to apply (e.g., ["t.email = <value>"])
   * @return bool True if the request limit is reached, false otherwise
   */
  public function checkRequestSpam(string $entityClass, string $alias = 't', $conditions)
  {
    $oneHourAgo = new \DateTime('-1 hour');

    $sql = $this->entityManager->createQueryBuilder()
      ->select("count($alias)")
      ->from($entityClass, $alias)
      ->where("$alias.isConsumed = :isConsumed")
      ->setParameter('isConsumed', false)
      ->andWhere("$alias.createdAt > :oneHourAgo")
      ->setParameter('oneHourAgo', $oneHourAgo);

    foreach ($conditions as $condition) {
      $sql->andWhere($condition);
    }

    $query = $sql->getQuery();
    $result = $query->getSingleScalarResult();

    return $result >= Constants::REQUEST_LIMIT;
  }
}
