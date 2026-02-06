<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Service\Attribute\Required;
use App\Config\Constants;
use App\Entity\UserEntity;

class BaseService
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

  /**
   * Check if the user sent too many requests for tokens (e.g., recovery tokens, verification tokens, etc.)
   * @param string $entityClass The entity class to check (e.g., UserEntity::class)
   * @param string $alias The alias to use in the query (default: 't')
   * @param array $conditions Additional conditions to apply (e.g., ["t.email = <value>"])
   * @return bool True if the request limit is reached, false otherwise
   */
  public function checkRequestSpam(string $entityClass, string $alias = 't', array $conditions)
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

  /**
   * Check if an email exist in user table
   * @param string $email Input email
   * @return bool True if the email exist in the user table, otherwise false
   */
  public function checkEmailExist(string $email)
  {
    $sql = $this->entityManager->createQueryBuilder()
      ->select('u')
      ->from(UserEntity::class, 'u')
      ->where('u.email = :email')
      ->setParameter("email", $email);

    $query = $sql->getQuery();
    $result = $query->execute();

    return count($result) > 0;
  }
}
