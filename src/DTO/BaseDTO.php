<?php

namespace App\DTO;

abstract class BaseDTO
{
  // Store the keys of the input associative array (data from $_POST or $_GET)
  // to return the errors (if any) back to the input HTML elements without having to convert them into any format
  private array $originalInputKey = [];

  public function getOriginalInputKey(): array
  {
    return $this->originalInputKey;
  }

  public function setOriginalInputKey(array $originalInputKey): self
  {
    $this->originalInputKey = $originalInputKey;

    return $this;
  }
}
