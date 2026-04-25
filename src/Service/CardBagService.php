<?php

namespace App\Service;

use App\DTO\BagNavigationTreeDTO;
use App\DTO\DeleteObjectDTO;
use App\DTO\NewBagDTO;
use App\DTO\NewCardDTO;
use App\Repository\CardBagRepository;
use App\Entity\CardBagEntity;
use App\Entity\CardEntity;
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
    $newBag->setUserEntity($user);
    $newBag->setParentCardBagEntity($parentCardBag);

    $this->entityManager->persist($newBag);
    $this->entityManager->flush();

    return $newBag;
  }

  public function addNewCard(NewCardDTO $dto)
  {
    // Get user entity through security
    $user = $this->security->getUser();
    // Get card bag entity
    $queryResult = $this->cardBagRepository->findBy(['id' => $dto->getBag()]);
    if (count($queryResult) > 0) {
      $parentCardBag = $queryResult[0];
    } else {
      $parentCardBag = null;
    }

    $newCard = new CardEntity();
    $newCard->setTitle($dto->getTitle());
    $newCard->setSubtitle($dto->getSubtitle());
    $newCard->setCardType($dto->getCardType());
    $newCard->setDescription($dto->getDescription());
    $newCard->setUserEntity($user);
    $newCard->setCardBagEntity($parentCardBag);
    $newCard->setCardColor($dto->getCardColor());
    $newCard->setCardTextColor($dto->getCardTextColor());

    $this->entityManager->persist($newCard);
    $this->entityManager->flush();
  }

  public function getBagList(?int $bagId): array
  {
    return $this->cardBagRepository->findBy(['parentCardBagEntity' => $bagId]);
  }

  public function getCardList(?int $bagId): array
  {
    return $this->cardRepository->findBy(['cardBagEntity' => $bagId]);
  }

  public function getBag(int $bagId)
  {
    return $this->cardBagRepository->find($bagId);
  }

  public function getBagTree(int $bagId): BagNavigationTreeDTO
  {
    $bag = $this->getBag($bagId);
    $bagDTO = new BagNavigationTreeDTO();
    $bagDTO->setBagId($bag->getId());
    $bagDTO->setBagName($bag->getName());

    $parentBag = $bag->getParentCardBagEntity();

    if ($parentBag != null) {
      return $this->getBagTree($parentBag->getId())->setChild($bagDTO);
    } else {
      return $bagDTO;
    }
  }

  public function deleteObject(DeleteObjectDTO $dto)
  {
    // Delete bags
    foreach ($dto->getBag() as $bagId) {
      $bag = $this->cardBagRepository->find($bagId);
      $this->entityManager->remove($bag);
    }
    // Delete cards
    foreach ($dto->getCard() as $cardId) {
      $card = $this->cardRepository->find($cardId);
      $this->entityManager->remove($card);
    }

    $this->entityManager->flush();
  }
}
