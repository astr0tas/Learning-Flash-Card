<?php

namespace App\Service;

use App\DTO\NewBagDTO;
use App\Repository\CardBagRepository;
use App\Entity\CardBagEntity;
use App\Repository\CardRepository;

class CardBagService extends BaseService
{

  public function __construct(private CardBagRepository $cardBagRepository, private CardRepository $cardRepository) {}

  public function checkDuplicationBagName(string $name, ?int $id): bool
  {
    $result = $this->cardBagRepository->findBy(['name' => $name, 'parentCardBagEntity' => $id]);

    return count($result) > 0;
  }

  public function addNewBag(NewBagDTO $newBagDTO): CardBagEntity
  {
    // Get user entity through security
    $user = $this->security->getUser();
    // Get parent card bag entity
    $queryResult = $this->cardBagRepository->findBy(['id' => $newBagDTO->getParentBag()]);
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

  public function getRootContent(): array
  {
    $result = [];

    $bagList = $this->cardBagRepository->findBy(['parentCardBagEntity' => null]);
    $cardList = $this->cardRepository->findBy(['cardBagEntity' => null]);

    $result = [...$bagList, ...$cardList];

    return $result;
  }

  public function getBagContent(int $id): array
  {
    $result = [];

    $bagList = $this->cardBagRepository->findBy(['parentCardBagEntity' => $id]);
    $cardList = $this->cardRepository->findBy(['cardBagEntity' => $id]);

    $result = [...$bagList, ...$cardList];

    return $result;
  }
}
