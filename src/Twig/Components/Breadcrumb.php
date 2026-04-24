<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Breadcrumb
{
  /**
   * @var array
   * Example:
   * [
   *   [
   *     'label' => 'Home',
   *     'url' => '/',
   *     'icon' => 'icons/example.svg', (use for `include` function)
   *   ],
   *   [
   *     'label' => 'Products',
   *     'url' => '/products'
   *   ]
   */
  public array $items = [];
}
