<?php

namespace App\ToolClass;

use App\Entity\TopicEntity;
use App\Entity\CardEntity;

class RestoreNode
{
  private bool $isRoot = false;
  // private ?CardEntity $card = null;
  private ?TopicEntity $topic = null;
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
   * Get the value of topic
   *
   * @return ?TopicEntity
   */
  public function getTopic(): ?TopicEntity
  {
    return $this->topic;
  }

  /**
   * Set the value of topic
   *
   * @param ?TopicEntity $topic
   *
   * @return self
   */
  public function setTopic(?TopicEntity $topic): self
  {
    $this->topic = $topic;

    return $this;
  }
}
