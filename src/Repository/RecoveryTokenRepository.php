<?php

namespace App\Repository;

use App\Entity\RecoveryTokenEntity;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

class RecoveryTokenRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, RecoveryTokenEntity::class);
  }

  public function getLatestToken(string $email)
  {
    $this->removeUnusedToken($email, false);

    return $this->createQueryBuilder('t')
      ->where('t.email = :email')
      ->andWhere('t.isConsumed = false')
      ->andWhere('t.expiresAt <= :now')
      ->orderBy('t.createdAt', 'DESC')
      ->setMaxResults(1)
      ->setParameters(new ArrayCollection(array(
        new Parameter('email', $email),
        new Parameter('now', new \DateTimeImmutable())
      )))
      ->getQuery()
      ->getOneOrNullResult();
  }

  private function removeUnusedToken(string $email, bool $removeFirstRecord = true)
  {
    $result = $this->createQueryBuilder('t')
      ->where('t.email = :email')
      ->andWhere('t.isConsumed = false')
      ->andWhere('t.expiresAt <= :now')
      ->orderBy('t.createdAt', 'DESC')
      ->setParameters(new ArrayCollection(array(
        new Parameter('email', $email),
        new Parameter('now', new \DateTimeImmutable())
      )))
      ->getQuery()
      ->getResult();

    foreach ($result as $index => $elem) {
      if ($index === 0 && $removeFirstRecord) {
        $this->entityManager->remove($elem);
      }

      if ($index !== 0) {
        $this->entityManager->remove($elem);
      }
    }
  }

  public function createToken(string $email, string $hashedToken)
  {
    $this->removeUnusedToken($email);

    $recoveryToken = new RecoveryTokenEntity();
    $recoveryToken->email = $email;
    $recoveryToken->token = $hashedToken;
    $recoveryToken->expiresAt = (new \DateTimeImmutable())->modify('+1 day');

    $this->entityManager->persist($recoveryToken);
  }
}
