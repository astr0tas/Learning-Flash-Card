<?php

namespace App\Service;

use App\Repository\UserRepository;

class AuthenticationService
{
  public function __construct(
    private EmailService $emailService,
    private UserRepository $userRepository
  ) {}
}
