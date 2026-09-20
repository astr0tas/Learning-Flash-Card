<?php

namespace App\Service;

use App\Config\Constants;
use App\Config\Routes;
use App\DTO\TopicNavigationTreeDTO;
use App\DTO\EditCardDTO;
use App\DTO\NewTopicDTO;
use App\DTO\NewCardDTO;
use App\DTO\SelectObjectDTO;
use App\Entity\TopicEntity;
use App\Entity\CardEntity;
use App\Repository\TopicRepository;
use App\Repository\CardRepository;
use DateTime;

class TopicService extends BaseService
{
  public function __construct(private TopicRepository $topicRepository, private CardRepository $cardRepository) {}

  public function getTopicByNameAndParentId(string $name, ?int $id): array
  {
    return $this->topicRepository->findBy(['name' => $name, 'parentTopicEntity' => $id, 'userEntity' => $this->user->getId()]);
  }

  public function addNewTopic(NewTopicDTO $newTopicDTO): TopicEntity
  {
    // Get user entity through security
    $user = $this->user;
    // Get parent topic entity
    $queryResult = $this->topicRepository->findBy(['id' => $newTopicDTO->getParentTopic()]);
    if (count($queryResult) > 0) {
      $parentTopic = $queryResult[0];
    } else {
      $parentTopic = null;
    }

    $newTopic = new TopicEntity();
    $newTopic->setName($newTopicDTO->getNewTopicName());
    $newTopic->setUserEntity($user);
    $newTopic->setParentTopicEntity($parentTopic);

    $this->entityManager->persist($newTopic);
    $this->entityManager->flush();

    return $newTopic;
  }

  public function addNewCard(NewCardDTO $dto)
  {
    // Get user entity through security
    $user = $this->user;
    // Get topic entity
    $queryResult = $this->topicRepository->findBy(['id' => $dto->getTopic()]);
    if (count($queryResult) > 0) {
      $parentTopic = $queryResult[0];
    } else {
      $parentTopic = null;
    }

    $newCard = new CardEntity();
    $newCard->setTitle($dto->getTitle());
    $newCard->setSubtitle($dto->getSubtitle() ?: null);
    $newCard->setCardType($dto->getCardType());
    $newCard->setDescription($dto->getDescription() ? strip_tags($dto->getDescription(), Constants::FLASH_CARD_DESCRIPTTION_ALLOW_TAGS) : null);
    $newCard->setUserEntity($user);
    $newCard->setTopicEntity($parentTopic);
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

  public function getTopicList(?int $parentTopicId, bool $preloadAssociations = true): array
  {
    $userId = $this->user->getId();
    if ($preloadAssociations) {
      return $this->topicRepository->findBy(['parentTopicEntity' => $parentTopicId, 'userEntity' => $userId]);
    }

    $query = $this->topicRepository->createQueryBuilder('t')
      ->where('t.userEntity = :userEntity')
      ->setParameter('userEntity', $userId);

    if ($parentTopicId !== null) {
      $query->andWhere('t.parentTopicEntity = :parentTopicEntity')->setParameter('parentTopicEntity', $parentTopicId);
    } else {
      $query->andWhere('t.parentTopicEntity IS NULL');
    }

    return $query->getQuery()->getArrayResult();
  }

  public function getCardList(?int $topicId): array
  {
    return $this->cardRepository->findBy(['topicEntity' => $topicId, 'userEntity' => $this->user->getId()]);
  }

  public function getTopic(int $topicId)
  {
    return $this->topicRepository->findOneBy(['id' => $topicId, 'userEntity' => $this->user->getId()]);
  }

  public function getCard(int $cardId)
  {
    return $this->cardRepository->findOneBy(['id' => $cardId, 'userEntity' => $this->user->getId()]);
  }

  public function getTopicTree(int $topicId): TopicNavigationTreeDTO
  {
    $currentTopic = $this->getTopic($topicId);
    $previousDTO = null;
    $currentDTO = null;

    while ($currentTopic) {
      $currentDTO = new TopicNavigationTreeDTO();
      $currentDTO->setTopicId($currentTopic->getId());
      $currentDTO->setTopicName($currentTopic->getName());
      $currentDTO->setChild($previousDTO);

      $previousDTO = $currentDTO;
      $parent = $currentTopic->getParentTopicEntity();
      $currentTopic = $parent ? $this->getTopic($parent->getId()) : null;
    }

    return $currentDTO;
  }

  private function deleteCard(int $cardId, \DateTimeInterface $deleteTime = new DateTime(), bool $moveToRoot = false)
  {
    $card = $this->cardRepository->find($cardId);
    if ($card) {
      // Update the card restore path
      $topic = $card->getTopicEntity();
      if ($topic) {
        $card->setRestorePath($this->parseTopicTreeToRestorePath($this->getTopicTree($topic->getId())));
      } else {
        $card->setRestorePath('/');
      }

      // Move the card to root if needed
      if ($moveToRoot) {
        $card->setTopicEntity(null);
      }

      // Soft delete the card
      $card->setDeletedAt($deleteTime);
    }
  }

  private function deleteTopic(int $topicId, \DateTimeInterface $deleteTime = new DateTime(), bool $moveToRoot = false)
  {
    $topic = $this->getTopic($topicId);
    if ($topic) {
      // Delete cards in the topic
      $cards = $topic->getCardEntities();
      foreach ($cards as $card) {
        $this->deleteCard($card->getId(), $deleteTime);
      }

      // Delete children topics recursively
      $childrenTopics = $topic->getChildrenTopicEntities();
      foreach ($childrenTopics as $childTopic) {
        $this->deleteTopic($childTopic->getId(), $deleteTime);
      }

      // Update the topic restore path
      $parentTopic = $topic->getParentTopicEntity();
      if ($parentTopic) {
        $topic->setRestorePath($this->parseTopicTreeToRestorePath($this->getTopicTree($parentTopic->getId())));
      } else {
        $topic->setRestorePath('/');
      }

      // Move the topic to root if needed
      if ($moveToRoot) {
        $topic->setParentTopicEntity(null);
      }

      // Delete the topic itself
      $topic->setDeletedAt($deleteTime);
    }
  }

  public function deleteObject(SelectObjectDTO $dto)
  {
    $deleteTime = new DateTime();

    // Delete cards
    foreach ($dto->getCard() as $cardId) {
      $this->deleteCard($cardId, $deleteTime, true);
    }

    // Delete topics
    foreach ($dto->getTopic() as $topicId) {
      $this->deleteTopic($topicId, $deleteTime, true);
    }

    $this->entityManager->flush();
  }

  public function moveObject(SelectObjectDTO $dto)
  {
    $newParentTopicId = $dto->getNewParentTopic();
    $newParentTopic = null;

    if ($newParentTopicId !== null) {
      $newParentTopic = $this->getTopic($newParentTopicId);
    }

    foreach ($dto->getTopic() as $topicId) {
      $this->moveTopic($this->getTopic($topicId), $newParentTopic);
    }

    foreach ($dto->getCard() as $cardId) {
      $this->moveCard($this->getCard($cardId), $newParentTopic);
    }

    $this->entityManager->flush();
  }

  public function parseTopicTreeToBreadcrumb(TopicNavigationTreeDTO $topicTree, array $breadcrumb = []): array
  {
    $runner = $topicTree;
    while ($runner) {
      $id = $runner->getTopicId();
      $breadcrumb[] = ['label' => $runner->getTopicName(), 'url' => str_replace('{id}', $id, Routes::TOPIC_DETAIL_ROUTE_URL), 'id' => $id];

      $runner = $runner->getChild();
    }
    return $breadcrumb;
  }

  private function moveCard(CardEntity $card, ?TopicEntity $newParentTopic = null): void
  {
    $oldParentTopic = $card->getTopicEntity();
    if ($oldParentTopic !== null) {
      $oldParentTopic->removeCard($card);
      $this->entityManager->persist($oldParentTopic);
    }

    $card->setTopicEntity($newParentTopic);
    $this->entityManager->persist($card);

    if ($newParentTopic !== null) {
      $newParentTopic->addCard($card);
      $this->entityManager->persist($newParentTopic);
    }
  }

  private function moveTopic(TopicEntity $topic, ?TopicEntity $newParentTopic = null): void
  {
    $newTopicChildrenTopics = $newParentTopic === null ? $this->getTopicList(null) : $newParentTopic->getChildrenTopicEntities();
    $duplicatedTopic = null;

    foreach ($newTopicChildrenTopics as $newTopicChildTopic) {
      if ($newTopicChildTopic->getName() === $topic->getName()) {
        $duplicatedTopic = $newTopicChildTopic;
        break;
      }
    }

    $oldTopicParent = $topic->getParentTopicEntity();
    if ($oldTopicParent !== null) {
      $oldTopicParent->removeChildTopicEntity($topic);
      $this->entityManager->persist($oldTopicParent);
    }

    if ($duplicatedTopic !== null) {
      $childTopicList = $topic->getChildrenTopicEntities()->toArray();
      foreach ($childTopicList as $childTopic) {
        $this->moveTopic($childTopic, $duplicatedTopic);
      }

      $childCardList = $topic->getCardEntities()->toArray();
      foreach ($childCardList as $card) {
        $this->moveCard($card, $duplicatedTopic);
      }

      $topic->setDeletedAt(new DateTime());
      $this->entityManager->remove($topic);
    } else {
      $topic->setParentTopicEntity($newParentTopic);
      $this->entityManager->persist($topic);

      if ($newParentTopic !== null) {
        $newParentTopic->addChildTopicEntity($topic);
        $this->entityManager->persist($newParentTopic);
      }
    }
  }

  private function parseTopicTreeToRestorePath(TopicNavigationTreeDTO $topicTree, string $str = ''): string
  {
    $runner = $topicTree;
    while ($runner) {
      $str = $str . '/' . $runner->getTopicName();
      $runner = $runner->getChild();
    }
    return $str;
  }
}
