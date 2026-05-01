<?php

namespace App\Service;

use App\Config\Constants;
use App\Config\Routes;
use App\DTO\BagNavigationTreeDTO;
use App\DTO\EditCardDTO;
use App\DTO\NewBagDTO;
use App\DTO\NewCardDTO;
use App\DTO\SelectObjectDTO;
use App\Entity\CardBagEntity;
use App\Entity\CardEntity;
use App\Repository\CardBagRepository;
use App\Repository\CardRepository;

class CardBagService extends BaseService
{
  public function __construct(private CardBagRepository $cardBagRepository, private CardRepository $cardRepository) {}

  public function getBagByNameAndParentId(string $name, ?int $id): array
  {
    return $this->cardBagRepository->findBy(['name' => $name, 'parentCardBagEntity' => $id, 'userEntity' => $this->user->getId()]);
  }

  public function addNewBag(NewBagDTO $newBagDTO): CardBagEntity
  {
    // Get user entity through security
    $user = $this->user;
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
    $user = $this->user;
    // Get card bag entity
    $queryResult = $this->cardBagRepository->findBy(['id' => $dto->getBag()]);
    if (count($queryResult) > 0) {
      $parentCardBag = $queryResult[0];
    } else {
      $parentCardBag = null;
    }

    $newCard = new CardEntity();
    $newCard->setTitle($dto->getTitle());
    $newCard->setSubtitle($dto->getSubtitle() ?: null);
    $newCard->setCardType($dto->getCardType());
    $newCard->setDescription($dto->getDescription() ? strip_tags($dto->getDescription(), Constants::FLASH_CARD_DESCRIPTTION_ALLOW_TAGS) : null);
    $newCard->setUserEntity($user);
    $newCard->setCardBagEntity($parentCardBag);
    $newCard->setCardColor($dto->getCardColor());
    $newCard->setCardTextColor($dto->getCardTextColor());

    $this->entityManager->persist($newCard);
    $this->entityManager->flush();
  }

  public function editCard(EditCardDTO $dto)
  {
    $card = $this->getCard($dto->getCard());

    $card->setTitle($dto->getTitle());
    $card->setSubtitle($dto->getSubtitle() ?: null);
    $card->setCardType($dto->getCardType());
    $card->setDescription($dto->getDescription() ? strip_tags($dto->getDescription(), Constants::FLASH_CARD_DESCRIPTTION_ALLOW_TAGS) : null);
    $card->setCardColor($dto->getCardColor());
    $card->setCardTextColor($dto->getCardTextColor());

    $this->entityManager->persist($card);
    $this->entityManager->flush();
  }

  public function getBagList(?int $bagId): array
  {
    return $this->cardBagRepository->findBy(['parentCardBagEntity' => $bagId, 'userEntity' => $this->user->getId()]);
  }

  public function getCardList(?int $bagId): array
  {
    return $this->cardRepository->findBy(['cardBagEntity' => $bagId, 'userEntity' => $this->user->getId()]);
  }

  public function getBag(int $bagId)
  {
    return $this->cardBagRepository->findOneBy(['id' => $bagId, 'userEntity' => $this->user->getId()]);
  }

  public function getCard(int $cardId)
  {
    return $this->cardRepository->findOneBy(['id' => $cardId, 'userEntity' => $this->user->getId()]);
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

  private function deleteCard(int $cardId, \DateTimeInterface $deleteTime = new \DateTime(), bool $moveToRoot = false)
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
    }
  }

  private function deleteBag(int $bagId, \DateTimeInterface $deleteTime = new \DateTime(), bool $moveToRoot = false)
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

  private function parseBagTreeToRestorePath(BagNavigationTreeDTO $bagTree, string $str = ''): string
  {
    $runner = $bagTree;
    while ($runner) {
      $str = $str . '/' . $runner->getBagName();
      $runner = $runner->getChild();
    }
    return $str;
  }
}
