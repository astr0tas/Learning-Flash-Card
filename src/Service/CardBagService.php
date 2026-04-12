<?php

namespace App\Service;

use App\DTO\NewBagDTO;
use App\Repository\CardBagRepository;
use App\Entity\CardBagEntity;

class CardBagService extends BaseService
{
  private CardBagRepository $cardBagRepository;

  public function checkDuplicationBagName(string $name): bool
  {
    $result = $this->cardBagRepository->findBy(['title' => $name]);

    return count($result) > 0;
  }

  public function addNewBag(NewBagDTO $newBagDTO): CardBagEntity
  {
    $user = $this->security->getUser();

    $newBag = new CardBagEntity();
    $newBag->setTitle($newBagDTO->getNewBagName());
    $newBag->setDescription($newBagDTO->getNewBagDescription());
    $newBag->setBagType($newBagDTO->getNewBagType());
    $newBag->setUserEntity($user);

    $this->entityManager->persist($newBag);
    $this->entityManager->flush();

    return $newBag;
  }
}
