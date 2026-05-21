<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ClickInput
{
  public ?string $name = null;
  public ?string $id = null;
  public ?string $label = null;
  public ?string $type = null;
  public string $element_class;
  public string $size = "base";

  public function mount(?string $type = null, string $size = "base"): void
  {
    $this->type = $type;
    $this->size = $size;

    $this->element_class = match ($type) {
      "checkbox" => "",
      "radio" => "",
      default => "",
    };

    $this->element_class .= match ($size) {
      "sm" => "h-3 w-3",
      "lg" => "h-5 w-5",
      default => "h-4 w-4",
    };
  }
}
