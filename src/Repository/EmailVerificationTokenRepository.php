<?php

namespace App\Repository;

use App\Entity\EmailVerificationTokenEntity;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

class EmailVerificationTokenRepository extends BaseRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, EmailVerificationTokenEntity::class);
  }

  public function getLatestToken(string $email)
  {
    return $this->createQueryBuilder('t')
      ->where('t.email = :email')
      ->andWhere('t.isConsumed = false')
      ->andWhere('t.expiresAt >= :now')
      ->orderBy('t.createdAt', 'DESC')
      ->setMaxResults(1)
      ->setParameters(new ArrayCollection(array(
        new Parameter('email', $email),
        new Parameter('now', new \DateTimeImmutable())
      )))
      ->getQuery()
      ->getOneOrNullResult();
  }

  public function createToken(string $email, string $hashedToken)
  {
    $verifyToken = new EmailVerificationTokenEntity();
    $verifyToken->email = $email;
    $verifyToken->token = $hashedToken;
    $verifyToken->expiresAt = (new \DateTimeImmutable())->modify('+7 days');

    $this->entityManager->persist($verifyToken);
  }

  public function clearUnusedTokens(string $email)
  {
    $result = $this->findBy(['email' => $email]);

    foreach ($result as $elem) {
      if (!$elem->isConsumed) {
        $elem->token = null;
      }
    }
  }
}
