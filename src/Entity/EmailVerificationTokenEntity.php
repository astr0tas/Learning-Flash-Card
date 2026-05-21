<?php

namespace App\Entity;

use App\Config\Constants;
use App\Repository\EmailVerificationTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailVerificationTokenRepository::class)]
#[ORM\Table(name: Constants::TABLE_EMAIL_VERIFICATION_TOKEN)]
class EmailVerificationTokenEntity extends TokenEntity {}
