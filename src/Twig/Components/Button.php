<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Button
{
  public string $variant = "";
  public string $element_class;

  public function mount(string $variant): void
  {
    $this->element_class = match ($variant) {
      "primary" => "bg-[var(--blue)] hover:opacity-80 text-white font-semibold py-2 px-4 rounded",
      "secondary" => "border-1 border-gray-500 hover:opacity-80 font-semibold py-2 px-4 rounded",
      default => "",
    };
  }
}
