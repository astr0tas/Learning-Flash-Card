<?php

namespace App\Utility;

use App\Config\Constants;

class Utility
{
  /**
   * Generate a random token string
   * @param int $length Length of the random bytes before hex encoding
   * @return string Hexadecimal representation of the random token, twice the length of input bytes
   */
  public static function generateRandomToken(int $length = 32): string
  {
    return bin2hex(random_bytes($length));
  }

  /**
   * Hash a given string using Bcrypt algorithm
   * @param string $input The input string to hash
   * @return string The Bcrypt hashed string
   */
  public static function hashString(string $input): string
  {
    return password_hash($input, PASSWORD_BCRYPT, [
      'cost' => Constants::BCRYPT_COST,
    ]);
  }

}
