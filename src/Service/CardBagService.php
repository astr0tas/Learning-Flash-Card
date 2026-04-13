<?php

namespace App\Service;

use App\DTO\NewBagDTO;
use App\Repository\CardBagRepository;
use App\Entity\CardBagEntity;

class CardBagService extends BaseService
{

  public function __construct(private CardBagRepository $cardBagRepository) {}

  public function checkDuplicationBagName(string $name): bool
  {
    $result = $this->cardBagRepository->findBy(['name' => $name]);

    return count($result) > 0;
  }

  public function addNewBag(NewBagDTO $newBagDTO): CardBagEntity
  {
    // Get user entity through security
    $user = $this->security->getUser();
    // Get parent card bag entity
    $queryResult = $this->cardBagRepository->findBy(['id' => $newBagDTO->getParentCard()]);
    if (count($queryResult) > 0) {
      $parentCardBag = $queryResult[0];
    } else {
      $parentCardBag = null;
    }

    $newBag = new CardBagEntity();
    $newBag->setName($newBagDTO->getNewBagName());
    $newBag->setDescription($newBagDTO->getNewBagDescription());
    $newBag->setUserEntity($user);
    $newBag->setParentCardBagEntity($parentCardBag);

    $this->entityManager->persist($newBag);
    $this->entityManager->flush();

    return $newBag;
  }
}
