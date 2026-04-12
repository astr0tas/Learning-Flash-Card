<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Select
{
  public ?string $name = null;
  public ?string $id = null;
  public ?string $label = null;
  public ?string $error = null;
  public bool $required = false;
}
