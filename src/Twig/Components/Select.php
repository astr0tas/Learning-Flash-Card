<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/fields/Select.html.twig')]
final class Select
{
  public ?string $name = null;
  public ?string $id = null;
  public ?string $label = null;
  public ?string $error = null;
  public bool $required = false;
  public string $defaultValue = "";
}
