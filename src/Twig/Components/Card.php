<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Config\Constants;

#[AsTwigComponent]
final class Card
{
  public int $cardId;
  public string $cardTitle;
  public ?string $cardSubTitle = null;
  public ?string $cardDescription = null;
  public ?string $model = null;
  public string $cardColor = Constants::FLASH_CARD_DEFAULT_COLOR;
  public string $cardTextColor = Constants::FLASH_CARD_DEFAULT_TEXT_COLOR;
}
