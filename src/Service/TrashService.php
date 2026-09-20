<?php

namespace App\Service;

use App\Config\Routes;
use App\DTO\TopicNavigationTreeDTO;
use App\DTO\SelectObjectDTO;
use App\Entity\TopicEntity;
use App\Entity\CardEntity;
use App\Repository\TopicRepository;
use App\Repository\CardRepository;
use App\ToolClass\RestoreNode;

class TrashService extends BaseService
{
  public function __construct(private TopicRepository $topicRepository, private CardRepository $cardRepository) {}

  public function getActiveTopicByNameAndParent(string $name, ?int $id): array
  {
    $query = $this->topicRepository->createQueryBuilder('t')
      ->where('t.deletedAt IS NULL')
      ->andWhere('t.name = :name')
      ->setParameter('name', $name)
      ->andWhere('t.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

    if ($id !== null) {
      $query->andWhere('t.parentTopicEntity = :topicId')
        ->setParameter('topicId', $id);
    } else {
      $query->andWhere('t.parentTopicEntity IS NULL');
    }

    return $query->getQuery()->getResult();
  }

  public function getTopicList(?int $topicId): array
  {
    $query = $this->topicRepository->createQueryBuilder('t')
      ->where('t.deletedAt IS NOT NULL')
      ->andWhere('t.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

    if ($topicId !== null) {
      $query->andWhere('t.parentTopicEntity = :topicId')
        ->setParameter('topicId', $topicId);
    } else {
      $query->andWhere('t.parentTopicEntity IS NULL');
    }

    return $query->getQuery()->getResult();
  }

  public function getCardList(?int $topicId): array
  {
    $query = $this->cardRepository->createQueryBuilder('c')
      ->where('c.deletedAt IS NOT NULL')
      ->andWhere('c.userEntity = :userId')
      ->setParameter('userId', $this->user->getId());

    if ($topicId !== null) {
      $query->andWhere('c.topicEntity = :topicId')
        ->setParameter('topicId', $topicId);
    } else {
      $query->andWhere('c.topicEntity IS NULL');
    }

    return $query->getQuery()->getResult();
  }

  public function getTopic(int $topicId)
  {
    $query = $this->topicRepository->createQueryBuilder('t')
      ->where('t.deletedAt IS NOT NULL')
      ->andWhere('t.id = :topicId')
      ->setParameter('topicId', $topicId)
      ->andWhere('t.userEntity = :userId')
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

  public function deleteObjectPermanet(SelectObjectDTO $dto): void
  {
    $this->disableSoftDeleteFilter();

    foreach ($dto->getTopic() as $topicId) {
      $topic = $this->getTopic($topicId);
      if ($topic) {
        $this->entityManager->remove($topic);
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

  private function restoreTopic(TopicEntity $topic, RestoreNode $root): void
  {
    $topicRestorePath = $topic->getRestorePath();

    if ($topicRestorePath === '/') {
      $topic->setDeletedAt(null);
      $topic->setRestorePath(null);
      $topic->setParentTopicEntity(null);

      $this->entityManager->persist($topic);

      $newChild = new RestoreNode();
      $newChild->setTopic($topic);
      $root->addChild($newChild);

      foreach ($topic->getChildrenTopicEntities() as $childTopic) {
        $this->restoreTopic($childTopic, $root);
      }

      foreach ($topic->getCardEntities() as $childCard) {
        $this->restoreCard($childCard, $root);
      }

      return;
    }

    $topicTree = explode('/', $topicRestorePath);
    $runner = $root;

    foreach ($topicTree as $topicName) {
      if ($topicName === '') {
        continue;
      }

      $matchingChildNode = null;

      foreach ($runner->getChildren() as $child) {
        if (($topicEntity = $child->getTopic()) && ($topicEntity->getName() === $topicName)) {
          $matchingChildNode = $child;
          break;
        }
      }


      if ($matchingChildNode !== null) {
        $runner = $matchingChildNode;
      } else {
        $nodeTopic = $runner->getTopic();
        $queryResult = $this->getActiveTopicByNameAndParent($topicName, $nodeTopic?->getId());
        $newNodeTopic = null;

        if (count($queryResult) > 0) {
          $newNodeTopic = $queryResult[0];
        } else {
          $newTopic = new TopicEntity();
          $newTopic->setName($topicName);
          $newTopic->setUserEntity($this->user);
          $newTopic->setParentTopicEntity($nodeTopic);

          $this->entityManager->persist($newTopic);

          if ($nodeTopic) {
            $nodeTopic->addChildTopicEntity($newTopic);

            $this->entityManager->persist($nodeTopic);
          }

          $newNodeTopic = $newTopic;
        }

        $newNodeChild = new RestoreNode();
        $newNodeChild->setTopic($newNodeTopic);
        $runner->addChild($newNodeChild);

        $runner = $newNodeChild;
      }
    }

    $nodeTopic = $runner->getTopic();
    $queryResult = $this->getActiveTopicByNameAndParent($topic->getName(), $nodeTopic?->getId());
    $newRestoreNode = new RestoreNode();

    $childrenTopics = $topic->getChildrenTopicEntities()->toArray();
    $childrenCards = $topic->getCardEntities()->toArray();

    if (count($queryResult) > 0) {
      $runner->addChild($newRestoreNode->setTopic($queryResult[0]));
    } else {
      $topic->setDeletedAt(null);
      $topic->setRestorePath(null);
      $topic->setParentTopicEntity($nodeTopic);

      $this->entityManager->persist($topic);

      $runner->addChild($newRestoreNode->setTopic($topic));
    }

    foreach ($childrenTopics as $childTopic) {
      $this->restoreTopic($childTopic, $root);
    }

    foreach ($childrenCards as $childCard) {
      $this->restoreCard($childCard, $root);
    }

    if (count($queryResult) > 0) {
      foreach ($childrenTopics as $childTopic) {
        $topic->removeChildTopicEntity($childTopic);
      }

      foreach ($childrenCards as $childCard) {
        $topic->removeCard($childCard);
      }

      $this->entityManager->remove($topic);
    }
  }

  private function restoreCard(CardEntity $card, RestoreNode $root): void
  {
    $cardRestorePath = $card->getRestorePath();

    if ($cardRestorePath === '/') {
      $card->setDeletedAt(null);
      $card->setRestorePath(null);
      $card->setTopicEntity(null);

      $this->entityManager->persist($card);
      return;
    }

    $topicTree = explode('/', $cardRestorePath);
    $runner = $root;

    foreach ($topicTree as $topicName) {
      if ($topicName === '') {
        continue;
      }

      $matchingChildNode = null;

      foreach ($runner->getChildren() as $child) {
        if (($topicEntity = $child->getTopic()) && ($topicEntity->getName() === $topicName)) {
          $matchingChildNode = $child;
          break;
        }
      }


      if ($matchingChildNode !== null) {
        $runner = $matchingChildNode;
      } else {
        $nodeTopic = $runner->getTopic();
        $queryResult = $this->getActiveTopicByNameAndParent($topicName, $nodeTopic?->getId());
        $newNodeTopic = null;

        if (count($queryResult) > 0) {
          $newNodeTopic = $queryResult[0];
        } else {
          $newTopic = new TopicEntity();
          $newTopic->setName($topicName);
          $newTopic->setUserEntity($this->user);
          $newTopic->setParentTopicEntity($nodeTopic);

          $this->entityManager->persist($newTopic);

          if ($nodeTopic) {
            $nodeTopic->addChildTopicEntity($newTopic);

            $this->entityManager->persist($nodeTopic);
          }

          $newNodeTopic = $newTopic;
        }

        $newNodeChild = new RestoreNode();
        $newNodeChild->setTopic($newNodeTopic);
        $runner->addChild($newNodeChild);

        $runner = $newNodeChild;
      }
    }

    $card->setDeletedAt(null);
    $card->setRestorePath(null);

    $nodeTopic = $runner->getTopic();
    $cardTopic = $card->getTopicEntity();

    if ($cardTopic === null || $cardTopic->getId() === $nodeTopic->getId()) {
      $card->setTopicEntity($nodeTopic);
    } else {
      // Remove association between the card and its old topic
      $cardTopic->removeCard($card);

      // Move the card to the new topic
      $nodeTopic->addCard($card);
      $card->setTopicEntity($nodeTopic);

      // $this->entityManager->persist($nodeTopic);
      $this->entityManager->remove($cardTopic);
    }

    $this->entityManager->persist($card);
  }

  public function restoreObject(SelectObjectDTO $dto): void
  {
    $root = new RestoreNode();

    $this->disableSoftDeleteFilter();

    foreach ($dto->getTopic() as $topicId) {
      $topic = $this->getTopic($topicId);
      if ($topic) {
        $this->restoreTopic($topic, $root);
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

  public function parseTopicTreeToBreadcrumb(TopicNavigationTreeDTO $topicTree, array $breadcrumb = []): array
  {
    $runner = $topicTree;
    while ($runner) {
      $breadcrumb[] = ['label' => $runner->getTopicName(), 'url' => str_replace('{id}', $runner->getTopicId(), Routes::TRASH_TOPIC_ROUTE_URL)];

      $runner = $runner->getChild();
    }
    return $breadcrumb;
  }
}
