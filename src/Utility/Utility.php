<?php

namespace App\Utility;

use App\Config\Constants;

class Utility
{
  public static function generateRandomToken(int $length = 32): string
  {
    return bin2hex(random_bytes($length));
  }

  public static function hashString(string $input): string
  {
    return password_hash($input, PASSWORD_BCRYPT, [
      'cost' => Constants::BCRYPT_COST,
    ]);
  }

  public static function parseGSIState(string $state): array
  {
    $result = [];
    $pairs = explode('&', $state);
    foreach ($pairs as $pair) {
      [$key, $value] = explode('=', $pair, 2);
      $result[$key] = $value;
    }
    return $result;
  }
}
