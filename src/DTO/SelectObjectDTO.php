<?php

namespace App\DTO;

class SelectObjectDTO extends BaseDTO
{
  private array $topic = [];
  private array $card = [];
  private ?int $newParentTopic = null;

  /**
   * Get the value of topic
   *
   * @return array
   */
  public function getTopic(): array
  {
    return $this->topic;
  }

  /**
   * Set the value of topic
   *
   * @param array $topic
   *
   * @return self
   */
  public function setTopic(array $topic): self
  {
    $this->topic = $topic;

    return $this;
  }

  /**
   * Get the value of card
   *
   * @return array
   */
  public function getCard(): array
  {
    return $this->card;
  }

  /**
   * Set the value of card
   *
   * @param array $card
   *
   * @return self
   */
  public function setCard(array $card): self
  {
    $this->card = $card;

    return $this;
  }

  /**
   * Get the value of newParentTopic
   *
   * @return ?int
   */
  public function getNewParentTopic(): ?int
  {
    return $this->newParentTopic;
  }

  /**
   * Set the value of newParentTopic
   *
   * @param int|string|null $newParentTopic
   *
   * @return self
   */
  public function setNewParentTopic(int|string|null $newParentTopic): self
  {
    if ($newParentTopic === '') {
      $newParentTopic = null;
    }

    $this->newParentTopic = $newParentTopic;

    return $this;
  }
}
