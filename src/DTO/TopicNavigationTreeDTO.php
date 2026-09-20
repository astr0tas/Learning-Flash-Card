<?php

namespace App\DTO;

class TopicNavigationTreeDTO extends BaseDTO
{
  private int $topicId;
  private string $topicName;
  private ?TopicNavigationTreeDTO $child = null;

  /**
   * Get the value of child
   *
   * @return ?TopicNavigationTreeDTO
   */
  public function getChild(): ?TopicNavigationTreeDTO
  {
    return $this->child;
  }

  /**
   * Set the value of child
   *
   * @param ?TopicNavigationTreeDTO $child
   *
   * @return self
   */
  public function setChild(?TopicNavigationTreeDTO $child): self
  {
    $this->child = $child;

    return $this;
  }

  /**
   * Get the value of topicName
   *
   * @return string
   */
  public function getTopicName(): string
  {
    return $this->topicName;
  }

  /**
   * Set the value of topicName
   *
   * @param string $topicName
   *
   * @return self
   */
  public function setTopicName(string $topicName): self
  {
    $this->topicName = $topicName;

    return $this;
  }

  /**
   * Get the value of topicId
   *
   * @return int
   */
  public function getTopicId(): int
  {
    return $this->topicId;
  }

  /**
   * Set the value of topicId
   *
   * @param int $topicId
   *
   * @return self
   */
  public function setTopicId(int $topicId): self
  {
    $this->topicId = $topicId;

    return $this;
  }
}
