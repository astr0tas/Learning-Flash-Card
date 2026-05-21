<?php

namespace App\ToolClass;

use App\Entity\CardBagEntity;
use App\Entity\CardEntity;

class RestoreNode
{
  private bool $isRoot = false;
  // private ?CardEntity $card = null;
  private ?CardBagEntity $cardBag = null;
  /**
   * @var RestoreNode[]
   */
  private array $children = [];

  /**
   * Get the value of children
   *
   * @return RestoreNode[]
   */
  public function getChildren(): array
  {
    return $this->children;
  }

  /**
   * Set the value of children
   *
   * @param RestoreNode[] $children
   *
   * @return self
   */
  public function setChildren(array $children): self
  {
    $this->children = $children;

    return $this;
  }

  /**
   * Add another child to node's children array
   * @param RestoreNode $node
   * @return RestoreNode
   */
  public function addChild(RestoreNode $node): self
  {
    $this->children[] = $node;

    return $this;
  }

  /**
   * Get the value of isRoot
   *
   * @return bool
   */
  public function getIsRoot(): bool
  {
    return $this->isRoot;
  }

  /**
   * Set the value of isRoot
   *
   * @param bool $isRoot
   *
   * @return self
   */
  public function setIsRoot(bool $isRoot): self
  {
    $this->isRoot = $isRoot;

    return $this;
  }

  // /**
  //  * Get the value of card
  //  *
  //  * @return ?CardEntity
  //  */
  // public function getCard(): ?CardEntity
  // {
  //   return $this->card;
  // }

  // /**
  //  * Set the value of card
  //  *
  //  * @param ?CardEntity $card
  //  *
  //  * @return self
  //  */
  // public function setCard(?CardEntity $card): self
  // {
  //   $this->card = $card;

  //   return $this;
  // }

  /**
   * Get the value of cardBag
   *
   * @return ?CardBagEntity
   */
  public function getCardBag(): ?CardBagEntity
  {
    return $this->cardBag;
  }

  /**
   * Set the value of cardBag
   *
   * @param ?CardBagEntity $cardBag
   *
   * @return self
   */
  public function setCardBag(?CardBagEntity $cardBag): self
  {
    $this->cardBag = $cardBag;

    return $this;
  }
}
