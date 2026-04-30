<?php

namespace App\Service;

use App\DTO\BagNavigationTreeDTO;
use App\DTO\SelectObjectDTO;
use App\Entity\CardBagEntity;
use App\Entity\CardEntity;
use App\Repository\CardBagRepository;
use App\Repository\CardRepository;

class TrashService extends BaseService
{
  public function __construct(private CardBagRepository $cardBagRepository, private CardRepository $cardRepository) {}

  public function getActiveBagByNameAndParent(string $name, ?int $id): array
  {
    $query = $this->cardBagRepository->createQueryBuilder('cb')
      ->where('cb.deletedAt IS NULL')
      ->andWhere('cb.name = :name')
      ->setParameter('name', $name);

    if ($id !== null) {
      $query->andWhere('cb.parentCardBagEntity = :bagId')
        ->setParameter('bagId', $id);
    } else {
      $query->andWhere('cb.parentCardBagEntity IS NULL');
    }

    return $query->getQuery()->getResult();
  }

  public function getBagList(?int $bagId): array
  {
    $query = $this->cardBagRepository->createQueryBuilder('cb')
      ->where('cb.deletedAt IS NOT NULL');

    if ($bagId !== null) {
      $query->andWhere('cb.parentCardBagEntity = :bagId')
        ->setParameter('bagId', $bagId);
    } else {
      $query->andWhere('cb.parentCardBagEntity IS NULL');
    }

    return $query->getQuery()->getResult();
  }

  public function getCardList(?int $bagId): array
  {
    $query = $this->cardRepository->createQueryBuilder('c')
      ->where('c.deletedAt IS NOT NULL');

    if ($bagId !== null) {
      $query->andWhere('c.cardBagEntity = :bagId')
        ->setParameter('bagId', $bagId);
    } else {
      $query->andWhere('c.cardBagEntity IS NULL');
    }

    return $query->getQuery()->getResult();
  }

  public function getBag(int $bagId)
  {
    $query = $this->cardBagRepository->createQueryBuilder('cb')
      ->where('cb.deletedAt IS NOT NULL')
      ->andWhere('cb.id = :bagId')
      ->setParameter('bagId', $bagId);

    return $query->getQuery()->getOneOrNullResult();
  }

  public function getCard(int $cardId)
  {
    $query = $this->cardRepository->createQueryBuilder('c')
      ->where('c.deletedAt IS NOT NULL')
      ->andWhere('c.id = :cardId')
      ->setParameter('cardId', $cardId);

    return $query->getQuery()->getOneOrNullResult();
  }

  public function getBagTree(int $bagId): BagNavigationTreeDTO
  {
    $currentBag = $this->getBag($bagId);
    $previousDTO = null;
    $currentDTO = null;

    while ($currentBag) {
      $currentDTO = new BagNavigationTreeDTO();
      $currentDTO->setBagId($currentBag->getId());
      $currentDTO->setBagName($currentBag->getName());
      $currentDTO->setChild($previousDTO);

      $previousDTO = $currentDTO;
      $parent = $currentBag->getParentCardBagEntity();
      $currentBag = $parent ? $this->getBag($parent->getId()) : null;
    }

    return $currentDTO;
  }

  public function deleteObjectPermanet(SelectObjectDTO $dto): void
  {
    $this->disableSoftDeleteFilter();

    foreach ($dto->getBag() as $bagId) {
      $bag = $this->getBag($bagId);
      if ($bag) {
        $this->entityManager->remove($bag);
      }
    }

    foreach ($dto->getCard() as $cardId) {
      $card = $this->getCard($cardId);
      if ($card) {
        $this->entityManager->remove($card);
      }
    }

    $this->entityManager->flush();

    $this->enableSoftDeleteFilter();
  }

  private function processRestorePath(string $restorePath, ?CardBagEntity $parentBag = null): ?CardBagEntity
  {
    // When the `restorePath` string is empty, it means that all the bags have been confirmed to be active or recreated
    // Return the `parentBag` which is also the last bag to be confirmed or recreated to the root caller
    if ($restorePath === '') {
      return $parentBag;
    }

    // Reformat bag names from the `restorePath` string into an array
    $bagNames = explode('/', $restorePath);
    // Pop the first element from the bag name array
    $bagName = array_shift($bagNames);
    // Confirm the existence of the bag
    $activeBags = $this->getActiveBagByNameAndParent($bagName, $parentBag ? $parentBag->getId() : null);
    // If exists, then continue to the next level of `restorePath`
    if (count($activeBags) > 0) {
      return $this->processRestorePath(implode('/', $bagNames), $activeBags[0]);
    }

    // If not exist (soft deleted or complete deletion from DB), recreate it and then continue to the next level of `restorePath`
    // Get user entity through security
    $user = $this->security->getUser();

    $newBag = new CardBagEntity();
    $newBag->setName($bagName);
    $newBag->setUserEntity($user);
    $newBag->setParentCardBagEntity($parentBag);

    $this->entityManager->persist($newBag);

    return $this->processRestorePath(implode('/', $bagNames), $newBag);
  }

  private function restoreBag(CardBagEntity $bag, bool $requireRestorePathProcess = false): void
  {
    $bagName = $bag->getName();
    $restorePath = $bag->getRestorePath();
    $restorePath = substr($restorePath, 1);
    $bagParent = $this->processRestorePath($restorePath);

    $activeBags = $this->getActiveBagByNameAndParent($bagName, $bagParent ? $bagParent->getId() : null);

    // If there is an active bag with the same name under the same parent bag, it means the bag in trash need to be permanently deleted
    // and all the child bags and cards under this bag will be moved to the active bag with the same name
    if (count($activeBags) > 0) {
      $targetActiveBag = $activeBags[0];

      $childrenBags = $bag->getChildrenCardBagEntities()->toArray();
      $childrenCards = $bag->getCardEntities()->toArray();

      // Move all the child bags and cards under this bag to the active bag with the same name recursively
      foreach ($childrenBags as $childBag) {
        $bag->removeChildCardBagEntity($childBag);

        $childBag->setParentCardBagEntity($targetActiveBag);
        $this->entityManager->persist($childBag);

        $targetActiveBag->addChildCardBagEntity($childBag);

        $this->restoreBag($childBag);
      }

      foreach ($childrenCards as $childCard) {
        $bag->removeCard($childCard);

        $childCard->setCardBagEntity($targetActiveBag);
        $this->entityManager->persist($childCard);

        $targetActiveBag->addCard($childCard);

        $this->restoreCard($childCard);
      }

      // Remove the bag in trash permanently
      $this->entityManager->remove($bag);
    } else {
      $bag->setRestorePath(null);
      $bag->setDeletedAt(null);

      if ($requireRestorePathProcess) {
        $bag->setParentCardBagEntity($bagParent);
      }

      $this->entityManager->persist($bag);

      // Restore all the child bags and cards under the bag recursively
      foreach ($bag->getChildrenCardBagEntities() as $childBag) {
        $this->restoreBag($childBag);
      }

      foreach ($bag->getCardEntities() as $card) {
        $this->restoreCard($card);
      }
    }
  }

  private function restoreCard(CardEntity $card): void
  {
    $restorePath = $card->getRestorePath();
    $restorePath = substr($restorePath, 1);

    $bag = $this->processRestorePath($restorePath);

    $card->setCardBagEntity($bag);

    $card->setRestorePath(null);
    $card->setDeletedAt(null);

    $this->entityManager->persist($card);
  }

  public function restoreObject(SelectObjectDTO $dto): void
  {
    $this->disableSoftDeleteFilter();

    foreach ($dto->getBag() as $bagId) {
      $bag = $this->getBag($bagId);
      if ($bag) {
        $this->restoreBag($bag, true);
      }
    }

    foreach ($dto->getCard() as $cardId) {
      $card = $this->getCard($cardId);
      if ($card) {
        $this->restoreCard($card);
      }
    }

    $this->entityManager->flush();

    $this->enableSoftDeleteFilter();
  }
}
