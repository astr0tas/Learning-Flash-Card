<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class BaseService
{
  protected Connection $connection;

  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
  }
}
