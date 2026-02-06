<?php

namespace App\Utility;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use function Symfony\Component\String\u;

class Utility
{
  // This will prevent the client using the "new" keyword since this class is static
  private function __construct() {}

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
   * Map data from an associative array to a DTO instance
   * @param array $data Associative array containing the data
   * @param object $instance DTO instance to be mapped
   */
  public static function mapArrayToDTO(array $data, object $instance)
  {
    foreach ($data as $key => $value) {
      $camelCaseKey = u($key)->camel()->toString();
      if (property_exists($instance, $camelCaseKey)) {
        $instance->{$camelCaseKey} = $value;
      }
    }
  }

  /**
   * Map data from a DTO instance to an associative array
   * @param object $instance DTO instance containing the data
   * @param array $existingData Associative array that might or might not have existing data beforehand
   * @return array Returned array containing old data from $existingData (if any) and new data from $instance (duplicated key-value pairs will be overwritten by $instance)
   */
  public static function mapDTOtoArray(object $instance, array $existingData = []): array
  {
    // Get instance's public properties and their values in the form of an associative array
    $dtoData = get_object_vars($instance);

    // Merge: DTO data overwrites existing data if keys match
    return array_merge($existingData, $dtoData);
  }

  /**
   * Validate input associative array data against specified constraints
   * @param array $input The input associative array data to validate
   * @param array  $fields An associative array mapping field names to their constraints (Assert\Collection).
   * @param array  $globals  An array of global constraints (e.g., Assert\Callback) to apply to the entire object.
   * @param bool $allowExtraFields Whether to allow extra fields not specified in $fields
   * @return array An array of validation errors, empty if none found
   */
  public static function validateInputArray(array $input, array $fields, array $globals = [], bool $allowExtraFields = true): array
  {
    if (!self::checkSubArrayInArray(array_keys($input), array_keys($fields))) {
      throw new \Exception(sprintf(
        "Input validation failed. Input contains invalid or unknown fields. Allowed fields are: %s",
        implode(', ', array_keys($fields))
      ));
    }

    $validator = Validation::createValidator();
    $violations = $validator->validate($input, [
      new Assert\Collection([
        'fields'           => $fields,
        'allowExtraFields' => $allowExtraFields,
      ]),
      ...$globals,
    ]);

    $formattedViolations = [];
    if (count($violations) > 0) {
      foreach ($violations as $violation) {
        // 1. Get the field name (e.g., "username")
        $field = $violation->getPropertyPath();
        $field = str_replace(['[', ']'], '', $field); // Clean up the field name

        // 2. Get the message (e.g., "This value is too short.")
        $message = $violation->getMessage();

        // 3. Store it. Use an array [] in case one field has multiple errors
        $formattedViolations[$field][] = $message;
      }
    }

    return $formattedViolations;
  }

  /**
   * Validate input DTO data against specified constraints.
   * Important: the keys in $fields must match with public properties of the DTO
   * @param object $instance The input DTO data to validate
   * @param array  $fields An associative array mapping field names to their constraints (Assert\Collection).
   * @param array  $globals  An array of global constraints (e.g., Assert\Callback) to apply to the entire object.
   * @param bool $allowExtraFields Whether to allow extra fields not specified in $fields
   * @return array An array of validation errors, empty if none found
   */
  public static function validateInputDTO(object $instance, array $fields, array $globals = [], bool $allowExtraFields = true): array
  {
    $arrayData = self::mapDTOtoArray($instance);

    return self::validateInputArray($arrayData, $fields, $globals, $allowExtraFields);
  }

  /**
   * Check if all the values in one array are present in another array
   * @param array $array Array to be checked against
   * @param array $subArray Array to check
   * @return bool True if all the values in $subArray are also present in $array, otherwise false
   */
  public static function checkSubArrayInArray(array $array, array $subArray): bool
  {
    foreach ($subArray as $elem) {
      if (!in_array($elem, $array)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Convert the keys in an associative array into snake case format
   * @param array $array Input associative array, can be camel case, snake case, kebab case or space case (words)
   * @return array Returned array with snake case keys
   */
  public static function setArrayKeyToSnakeCase(array $array): array
  {
    $result = [];

    foreach ($array as $key => $value) {
      $result[u($key)->snake()->toString()] = $value;
    }

    return $result;
  }
}
