<?php

namespace App\Twig\Components;

use App\Config\Routes;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Topic
{
  public int $topicId;
  public string $topicName;
  public string $href = "";
  public ?string $model = null;

  public function mount(int $topicId, string $topicName, ?string $model = null, string $href = ""): void
  {
    $this->topicId = $topicId;
    $this->topicName = $topicName;
    $this->model = $model;
    $this->href = $href;
  }
}
