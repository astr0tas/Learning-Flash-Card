<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/fields/Textarea.html.twig')]
final class Textarea
{
  public ?string $name = null;
  public ?string $id = null;
  public ?string $label = null;
  public ?string $error = null;
  public bool $required = false;
  public ?string $value = null;
  public ?string $defaultValue = null;
}
