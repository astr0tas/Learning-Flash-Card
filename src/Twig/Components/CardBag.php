<?php

namespace App\Twig\Components;

use App\Config\Routes;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class CardBag
{
  public int $bagId;
  public string $bagName;
  public ?string $bagDescription = null;
  public string $href = "";
  public ?string $model = null;

  public function mount(int $bagId, string $bagName, ?string $bagDescription = null, ?string $model = null): void
  {
    $this->bagId = $bagId;
    $this->bagName = $bagName;
    $this->bagDescription = $bagDescription;
    $this->model = $model;

    $this->href = str_replace('{id}', $bagId, Routes::CARD_BAG_DETAIL_ROUTE_URL);
  }
}
