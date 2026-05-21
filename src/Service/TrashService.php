<?php

namespace App\Service;

use App\Config\Routes;
use App\DTO\BagNavigationTreeDTO;
use App\DTO\SelectObjectDTO;
use App\Entity\CardBagEntity;
use App\Entity\CardEntity;
use App\Repository\CardBagRepository;
use App\Repository\CardRepository;
use App\ToolClass\RestoreNode;

class TrashService extends BaseService
{
  public function __construct(private CardBagRepository $cardBagRepository, private CardRepository $cardRepository) {}

  public function getActiveBagByNameAndParent(string $name, ?int $id): array
  {
    $query = $this->cardBagRepository->createQueryBuilder('cb')
      ->where('cb.deletedAt IS NULL')
      ->andWhere('cb.name = :name')
      ->setParameter('name', $name)
      ->andWhere('cb.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

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
      ->where('cb.deletedAt IS NOT NULL')
      ->andWhere('cb.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

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
      ->where('c.deletedAt IS NOT NULL')
      ->andWhere('c.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

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
      ->setParameter('bagId', $bagId)
      ->andWhere('cb.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

    return $query->getQuery()->getOneOrNullResult();
  }

  public function getCard(int $cardId)
  {
    $query = $this->cardRepository->createQueryBuilder('c')
      ->where('c.deletedAt IS NOT NULL')
      ->andWhere('c.id = :cardId')
      ->setParameter('cardId', $cardId)
      ->andWhere('c.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

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

  private function restoreBag(CardBagEntity $bag, RestoreNode $root): void
  {
    $bagRestorePath = $bag->getRestorePath();

    if ($bagRestorePath === '/') {
      $bag->setDeletedAt(null);
      $bag->setRestorePath(null);
      $bag->setParentCardBagEntity(null);

      $this->entityManager->persist($bag);

      $newChild = new RestoreNode();
      $newChild->setCardBag($bag);
      $root->addChild($newChild);

      foreach ($bag->getChildrenCardBagEntities() as $childBag) {
        $this->restoreBag($childBag, $root);
      }

      foreach ($bag->getCardEntities() as $childCard) {
        $this->restoreCard($childCard, $root);
      }

      return;
    }

    $bagTree = explode('/', $bagRestorePath);
    $runner = $root;

    foreach ($bagTree as $bagName) {
      if ($bagName === '') {
        continue;
      }

      $matchingChildNode = null;

      foreach ($runner->getChildren() as $child) {
        if (($bagEntity = $child->getCardBag()) && ($bagEntity->getName() === $bagName)) {
          $matchingChildNode = $child;
          break;
        }
      }


      if ($matchingChildNode !== null) {
        $runner = $matchingChildNode;
      } else {
        $nodeBag = $runner->getCardBag();
        $queryResult = $this->getActiveBagByNameAndParent($bagName, $nodeBag?->getId());
        $newNodeBag = null;

        if (count($queryResult) > 0) {
          $newNodeBag = $queryResult[0];
        } else {
          $newBag = new CardBagEntity();
          $newBag->setName($bagName);
          $newBag->setUserEntity($this->user);
          $newBag->setParentCardBagEntity($nodeBag);

          $this->entityManager->persist($newBag);

          if ($nodeBag) {
            $nodeBag->addChildCardBagEntity($newBag);

            $this->entityManager->persist($nodeBag);
          }

          $newNodeBag = $newBag;
        }

        $newNodeChild = new RestoreNode();
        $newNodeChild->setCardBag($newNodeBag);
        $runner->addChild($newNodeChild);

        $runner = $newNodeChild;
      }
    }

    $nodeCardBag = $runner->getCardBag();
    $queryResult = $this->getActiveBagByNameAndParent($bag->getName(), $nodeCardBag?->getId());
    $newRestoreNode = new RestoreNode();

    $childrenBags = $bag->getChildrenCardBagEntities()->toArray();
    $childrenCards = $bag->getCardEntities()->toArray();

    if (count($queryResult) > 0) {
      $runner->addChild($newRestoreNode->setCardBag($queryResult[0]));
    } else {
      $bag->setDeletedAt(null);
      $bag->setRestorePath(null);
      $bag->setParentCardBagEntity($nodeCardBag);

      $this->entityManager->persist($bag);

      $runner->addChild($newRestoreNode->setCardBag($bag));
    }

    foreach ($childrenBags as $childBag) {
      $this->restoreBag($childBag, $root);
    }

    foreach ($childrenCards as $childCard) {
      $this->restoreCard($childCard, $root);
    }

    if (count($queryResult) > 0) {
      foreach ($childrenBags as $childBag) {
        $bag->removeChildCardBagEntity($childBag);
      }

      foreach ($childrenCards as $childCard) {
        $bag->removeCard($childCard);
      }

      $this->entityManager->remove($bag);
    }
  }

  private function restoreCard(CardEntity $card, RestoreNode $root): void
  {
    $cardRestorePath = $card->getRestorePath();

    if ($cardRestorePath === '/') {
      $card->setDeletedAt(null);
      $card->setRestorePath(null);
      $card->setCardBagEntity(null);

      $this->entityManager->persist($card);
      return;
    }

    $bagTree = explode('/', $cardRestorePath);
    $runner = $root;

    foreach ($bagTree as $bagName) {
      if ($bagName === '') {
        continue;
      }

      $matchingChildNode = null;

      foreach ($runner->getChildren() as $child) {
        if (($bagEntity = $child->getCardBag()) && ($bagEntity->getName() === $bagName)) {
          $matchingChildNode = $child;
          break;
        }
      }


      if ($matchingChildNode !== null) {
        $runner = $matchingChildNode;
      } else {
        $nodeBag = $runner->getCardBag();
        $queryResult = $this->getActiveBagByNameAndParent($bagName, $nodeBag?->getId());
        $newNodeBag = null;

        if (count($queryResult) > 0) {
          $newNodeBag = $queryResult[0];
        } else {
          $newBag = new CardBagEntity();
          $newBag->setName($bagName);
          $newBag->setUserEntity($this->user);
          $newBag->setParentCardBagEntity($nodeBag);

          $this->entityManager->persist($newBag);

          if ($nodeBag) {
            $nodeBag->addChildCardBagEntity($newBag);

            $this->entityManager->persist($nodeBag);
          }

          $newNodeBag = $newBag;
        }

        $newNodeChild = new RestoreNode();
        $newNodeChild->setCardBag($newNodeBag);
        $runner->addChild($newNodeChild);

        $runner = $newNodeChild;
      }
    }

    $card->setDeletedAt(null);
    $card->setRestorePath(null);

    $nodeBag = $runner->getCardBag();
    $cardBag = $card->getCardBagEntity();

    if ($cardBag === null || $cardBag->getId() === $nodeBag->getId()) {
      $card->setCardBagEntity($nodeBag);
    } else {
      // Remove association between the card and its old bag
      $cardBag->removeCard($card);

      // Move the card to the new bag
      $nodeBag->addCard($card);
      $card->setCardBagEntity($nodeBag);

      // $this->entityManager->persist($nodeBag);
      $this->entityManager->remove($cardBag);
    }

    $this->entityManager->persist($card);
  }

  public function restoreObject(SelectObjectDTO $dto): void
  {
    $root = new RestoreNode();

    $this->disableSoftDeleteFilter();

    foreach ($dto->getBag() as $bagId) {
      $bag = $this->getBag($bagId);
      if ($bag) {
        $this->restoreBag($bag, $root);
      }
    }

    foreach ($dto->getCard() as $cardId) {
      $card = $this->getCard($cardId);
      if ($card) {
        $this->restoreCard($card, $root);
      }
    }

    $this->entityManager->flush();

    $this->enableSoftDeleteFilter();
  }

  public function parseBagTreeToBreadcrumb(BagNavigationTreeDTO $bagTree, array $breadcrumb = []): array
  {
    $runner = $bagTree;
    while ($runner) {
      $breadcrumb[] = ['label' => $runner->getBagName(), 'url' => str_replace('{id}', $runner->getBagId(), Routes::TRASH_BAG_ROUTE_URL)];

      $runner = $runner->getChild();
    }
    return $breadcrumb;
  }
}
