<?php

namespace App\Twig\Components;

use Symfony\Component\String\ByteString;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Modal
{
  public string $modalGeneratedId = '';
  public bool $displayCancelButton = true;
  public string $modalTitle = '';
  public string $actionButtonText = '';
  public string $actionButtonVariant = ''; // Use for the `variant` property of the `Button` component

  public function __construct()
  {
    $this->modalGeneratedId = ByteString::fromRandom(16)->toString();
  }
}
