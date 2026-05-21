<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Breadcrumb
{
  /**
   * @var array
   * Required key: label
   * Optional key: url, icon (HTML string), action
   * Example:
   * [
   *   [
   *     'label' => 'Home',
   *     'url' => '/',
   *     'icon' => '<svg></svg>'
   *   ],
   *   [
   *     'label' => 'Products',
   *     'url' => '/products'
   *   ],
   *   [
   *    'label' => 'Product Name',
   *    'action' => 'javascript:void(0);' (if no url, use action for click event)
   *   ]
   * ]
   */
  public array $items = [];
  public int $maximumDisplayableItems = 3;
}
