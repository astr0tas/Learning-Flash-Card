<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Input
{
  public ?string $name = null;
  public ?string $id = null;
  public ?string $label = null;
  public ?string $error = null;
  public bool $required = false;
  public string $type = "text";
  public ?string $value = null;
  public ?string $defaultValue = null;
}
