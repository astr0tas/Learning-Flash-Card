<?php

namespace App\Utility;

use App\DTO\BaseDTO;
use ReflectionObject;
use function Symfony\Component\String\u;

class ClassUtility
{
  /**
   * Map data from an associative array to a DTO instance
   * @param array $data Associative array containing the data
   * @param BaseDTO $instance DTO instance to be mapped
   */
  public static function mapArrayToDTO(array $data, BaseDTO $instance)
  {
    $instance->setOriginalInputKey(array_keys($data));

    $propertyNames = [];

    $reflection = new ReflectionObject($instance);

    foreach ($reflection->getProperties() as $property) {
      $propertyNames[] = $property->getName();
    }

    foreach ($data as $key => $value) {
      foreach ($propertyNames as $propertyName) {
        if (u($propertyName)->camel()->toString() === u($key)->camel()->toString()) {
          $method = self::getPropertySetterFunctionName($propertyName);

          if ($reflection->hasMethod($method)) {
            $instance->{$method}($value);
          }
        }
      }
    }
  }

  /**
   * Map data from a DTO instance to an associative array
   * @param BaseDTO $instance DTO instance containing the data
   * @param array $existingData Associative array that might or might not have existing data beforehand
   * @return array Returned array containing old data from $existingData (if any) and new data from $instance (duplicated key-value pairs will be overwritten by $instance)
   */
  public static function mapDTOtoArray(BaseDTO $instance, array $existingData = []): array
  {
    // Get instance properties and their values in the form of an associative array
    $dtoData = [];
    $reflection = new ReflectionObject($instance);
    foreach ($reflection->getProperties() as $property) {
      $name = $property->getName();
      $method = self::getPropertyGetterFunctionName($name);

      if ($reflection->hasMethod($method)) {
        $value = $instance->{$method}();

        $dtoData[$name] = $value;
      }
    }

    // Merge: DTO data overwrites existing data if keys match
    return array_merge($existingData, $dtoData);
  }

  /**
   * Validate input DTO data against specified constraints.
   * Important: the keys in $fields must match with public properties of the DTO
   * @param BaseDTO $instance The input DTO data to validate
   * @param array  $fields An associative array mapping field names to their constraints (Assert\Collection).
   * @param array  $globals  An array of global constraints (e.g., Assert\Callback) to apply to the entire object.
   * @param bool $allowExtraFields Whether to allow extra fields not specified in $fields
   * @return array An array of validation errors, empty if none found
   */
  public static function validateInputDTO(BaseDTO $instance, array $fields, array $globals = [], bool $allowExtraFields = true): array
  {
    $arrayData = self::mapDTOtoArray($instance);

    $errors = Utility::validateInputArray($arrayData, $fields, $globals, $allowExtraFields);

    $result = [];

    foreach ($errors as $key => $value) {
      foreach ($instance->getOriginalInputKey() as $inputKey) {
        if (u($inputKey)->camel()->toString() === u($key)->camel()->toString()) {
          $result[$inputKey] = $value;
        }
      }
    }

    return $result;
  }

  /**
   * Get the getter function name for a given property name (e.g., "username" -> "getUsername")
   * @param string $propertyName
   * @return string
   */
  public static function getPropertyGetterFunctionName(string $propertyName): string
  {
    return 'get' . ucfirst($propertyName);
  }

  /**
   * Get the setter function name for a given property name (e.g., "username" -> "setUsername")
   * @param string $propertyName
   * @return string
   */
  public static function getPropertySetterFunctionName(string $propertyName): string
  {
    return 'set' . ucfirst($propertyName);
  }

  public static function mapObject(object $sourceInstance, object $destinationInstance): void
  {
    $sourceReflection = new ReflectionObject($sourceInstance);
    $destinationReflection = new ReflectionObject($destinationInstance);
    foreach ($destinationReflection->getProperties() as $destinationProperty) {
      $destinationPropertyName = $destinationProperty->getName();

      if ($sourceReflection->hasProperty($destinationPropertyName)) {
        $sourceGetterMethod = self::getPropertyGetterFunctionName($destinationPropertyName);
        $destinationSetterMethod = self::getPropertySetterFunctionName($destinationPropertyName);

        if ($sourceReflection->hasMethod($sourceGetterMethod) && $destinationReflection->hasMethod($destinationSetterMethod)) {
          $destinationInstance->{$destinationSetterMethod}($sourceInstance->{$sourceGetterMethod});
        }
      }
    }
  }
}
