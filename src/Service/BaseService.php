<?php

namespace App\Service;

use App\Config\Constants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Bundle\SecurityBundle\Security;

class BaseService
{
  public TranslatorInterface $translator;
  public SessionInterface $session;
  public EntityManagerInterface $entityManager;
  public Security $security;

  #[Required]
  public function initProperties(TranslatorInterface $translator, RequestStack $requestStack, EntityManagerInterface $entityManager, Security $security)
  {
    $this->translator = $translator;
    $this->session = $requestStack->getSession();
    $this->entityManager = $entityManager;
    $this->security = $security;
  }

  public function getFlashBag(): FlashBagInterface|null
  {
    if ($this->session instanceof FlashBagAwareSessionInterface) {
      return $this->session->getFlashBag();
    }

    throw new InternalErrorException(Constants::NO_SESSION_FLASH_BAG);
  }

  public function enableSoftDeleteFilter()
  {
    $this->entityManager->getFilters()->enable('softdeleteable');
  }

  public function disableSoftDeleteFilter()
  {
    $this->entityManager->getFilters()->disable('softdeleteable');
  }

  // /**
  //  * Check if the user sent too many requests for tokens (e.g., recovery tokens, verification tokens, etc.)
  //  * @param string $entityClass The entity class to check (e.g., UserEntity::class)
  //  * @param string $alias The alias to use in the query (default: 't')
  //  * @param array $conditions Additional conditions to apply (e.g., ["t.email = <value>"])
  //  * @return bool True if the request limit is reached, false otherwise
  //  */
  // public function checkRequestSpam(string $entityClass, string $alias = 't', array $conditions)
  // {
  //   $oneHourAgo = new \DateTime('-1 hour');

  //   $sql = $this->entityManager->createQueryBuilder()
  //     ->select("count($alias)")
  //     ->from($entityClass, $alias)
  //     ->where("$alias.isConsumed = :isConsumed")
  //     ->setParameter('isConsumed', false)
  //     ->andWhere("$alias.createdAt > :oneHourAgo")
  //     ->setParameter('oneHourAgo', $oneHourAgo);

  //   foreach ($conditions as $condition) {
  //     $sql->andWhere($condition);
  //   }

  //   $query = $sql->getQuery();
  //   $result = $query->getSingleScalarResult();

  //   return $result >= Constants::REQUEST_LIMIT;
  // }
}
