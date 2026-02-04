<?php

namespace App\Utility;

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
   * Hash a given string using a specified algorithm
   * @param string $input The input string to hash
   * @param string $algorithm The hashing algorithm to use (default is SHA256)
   * @param array $options Options for the hashing algorithm
   * @return string The hashed string
   */
  public static function hashString(string $input, string $algorithm = 'sha256', array $options = []): string
  {
    return hash($algorithm, $input, false, $options);
  }

  /**
   * Compare a given string with a hash
   * @param string $input The input string to compare
   * @param string $hash The hash to compare against
   * @return bool True if the input matches the hash, false otherwise
   */
  public static function compareHash(string $input, string $hash): bool
  {
    return hash_equals($hash, $input);
  }

  /**
   * Convert an input string to camel case format (input string can be in kebab case, camel case, snake case or even spaced string)
   * @param string $string The input string to be converted to camel case
   * @return string Camel case conversion result of the input string
   */
  public static function toCamelCase(string $string)
  {
    // Replace hyphens and underscores with spaces, and convert to lowercase
    $string = str_replace(['-', '_'], ' ', strtolower($string));

    // Capitalize the first letter of each word (except for the very first letter of the string, handled later)
    $string = ucwords($string);

    // Remove all spaces
    $string = str_replace(' ', '', $string);

    // Make the first character of the entire string lowercase to achieve lower camel case
    $string = lcfirst($string);

    return $string;
  }

  public static function mapArrayToDTO(array $data, object $instance)
  {
    foreach ($data as $key => $value) {
      $camelCaseKey = self::toCamelCase($key);
      if (property_exists($instance, $camelCaseKey)) {
        $instance->{$camelCaseKey} = $value;
      }
    }
  }
}
