<?php

namespace App\DTO;

class NewTopicDTO extends BaseDTO
{
  private string $newTopicName;
  private ?int $parentTopic = null;

  /**
   * Get the value of newTopicName
   */
  public function getNewTopicName()
  {
    return $this->newTopicName;
  }

  /**
   * Set the value of newTopicName
   */
  public function setNewTopicName($newTopicName): self
  {
    $this->newTopicName = $newTopicName;

    return $this;
  }

  /**
   * Get the value of parentTopic
   *
   * @return ?int
   */
  public function getParentTopic(): ?int
  {
    return $this->parentTopic;
  }

  /**
   * Set the value of parentTopic
   *
   * @param ?int $parentTopic
   *
   * @return self
   */
  public function setParentTopic(?int $parentTopic): self
  {
    $this->parentTopic = $parentTopic;

    return $this;
  }
}
