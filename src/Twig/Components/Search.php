<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/fields/Search.html.twig')]
final class Search
{
  public ?string $name = null;
  public ?string $id = null;
  public ?string $label = null;
  public ?string $error = null;
  public bool $required = false;
  public ?string $value = null;
  public ?string $defaultValue = null;
}
