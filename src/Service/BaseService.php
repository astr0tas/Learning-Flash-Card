<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use App\Config\Constants;

class BaseService
{
  protected Connection $connection;

  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
  }

  public function getLocaleFromCookie(): string
  {
    if (empty($_COOKIE[Constants::LOCALE_COOKIE_NAME])) {
      return Constants::DEFAULT_LOCALE;
    }

    return \strval($_COOKIE[Constants::LOCALE_COOKIE_NAME]) ?? Constants::DEFAULT_LOCALE;
  }
}
