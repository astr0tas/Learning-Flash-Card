<?php

namespace App\Service;

use App\DTO\BagNavigationTreeDTO;
use App\DTO\SelectObjectDTO;
use App\DTO\NewBagDTO;
use App\DTO\NewCardDTO;
use App\Repository\CardBagRepository;
use App\Entity\CardBagEntity;
use App\Entity\CardEntity;
use App\Repository\CardRepository;
use App\Config\Routes;

class CardBagService extends BaseService
{
  public function __construct(private CardBagRepository $cardBagRepository, private CardRepository $cardRepository) {}

  public function getBagByNameAndParentId(string $name, ?int $id): array
  {
    return $this->cardBagRepository->findBy(['name' => $name, 'parentCardBagEntity' => $id]);
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

  public function deleteCard(int $cardId, \DateTimeInterface $deleteTime = new \DateTime(), bool $moveToRoot = false,  bool $flushAfterFinish = false)
  {
    $card = $this->cardRepository->find($cardId);
    if ($card) {
      // Update the card restore path
      $bag = $card->getCardBagEntity();
      if ($bag) {
        $card->setRestorePath($this->parseBagTreeToRestorePath($this->getBagTree($bag->getId())));
      } else {
        $card->setRestorePath('/');
      }

      // Move the card to root if needed
      if ($moveToRoot) {
        $card->setCardBagEntity(null);
      }

      // Soft delete the card
      $card->setDeletedAt($deleteTime);
      if ($flushAfterFinish) {
        $this->entityManager->flush();
      }
    }
  }

  public function deleteBag(int $bagId, \DateTimeInterface $deleteTime = new \DateTime(), bool $moveToRoot = false, bool $flushAfterFinish = false)
  {
    $bag = $this->getBag($bagId);
    if ($bag) {
      // Delete cards in the bag
      $cards = $bag->getCardEntities();
      foreach ($cards as $card) {
        $this->deleteCard($card->getId(), $deleteTime);
      }

      // Delete children bags recursively
      $childrenBags = $bag->getChildrenCardBagEntities();
      foreach ($childrenBags as $childBag) {
        $this->deleteBag($childBag->getId(), $deleteTime);
      }

      // Update the bag restore path
      $parentBag = $bag->getParentCardBagEntity();
      if ($parentBag) {
        $bag->setRestorePath($this->parseBagTreeToRestorePath($this->getBagTree($parentBag->getId())));
      } else {
        $bag->setRestorePath('/');
      }

      // Move the bag to root if needed
      if ($moveToRoot) {
        $bag->setParentCardBagEntity(null);
      }

      // Delete the bag itself
      $bag->setDeletedAt($deleteTime);

      if ($flushAfterFinish) {
        $this->entityManager->flush();
      }
    }
  }

  public function deleteObject(SelectObjectDTO $dto)
  {
    $deleteTime = new \DateTime();

    // Delete cards
    foreach ($dto->getCard() as $cardId) {
      $this->deleteCard($cardId, $deleteTime, true);
    }

    // Delete bags
    foreach ($dto->getBag() as $bagId) {
      $this->deleteBag($bagId, $deleteTime, true);
    }

    $this->entityManager->flush();
  }

  public function parseBagTreeToBreadcrumb(BagNavigationTreeDTO $bagTree, array $breadcrumb = []): array
  {
    $runner = $bagTree;
    while ($runner) {
      $breadcrumb[] = ['label' => $runner->getBagName(), 'url' => str_replace('{id}', $runner->getBagId(), Routes::CARD_BAG_DETAIL_ROUTE_URL)];

      $runner = $runner->getChild();
    }
    return $breadcrumb;
  }

  public function parseBagTreeToRestorePath(BagNavigationTreeDTO $bagTree, string $str = ''): string
  {
    $runner = $bagTree;
    while ($runner) {
      $str = $str . '/' . $runner->getBagName();
      $runner = $runner->getChild();
    }
    return $str;
  }
}
