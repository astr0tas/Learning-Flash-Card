<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Button
{
  public string $variant = "";
  public string $class = "text-base";
  public string $element_class;

  public function mount(string $variant, string $class): void
  {
    $this->element_class = match ($variant) {
      "primary" => "bg-(--blue) text-white font-semibold py-2 px-4 $class",
      "secondary" => "bg-(--gray) text-white font-semibold py-2 px-4 $class",
      "outline" => "border-1 border-gray-400 font-semibold py-2 px-4 $class",
      "danger" => "bg-(--red) text-white font-semibold py-2 px-4 $class",
      default => "",
    };
  }
}
