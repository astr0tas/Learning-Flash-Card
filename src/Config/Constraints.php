<?php

namespace App\Config;

// Every constraints of the database that the app itself needs to follow are defined here
class Constraints{
  public const CARD_BAG_NAME_MAX_LENGTH = 255;
  public const CARD_BAG_DESCRIPTION_MAX_LENGTH = 1000;
  public const CARD_TITLE_MAX_LENGTH = 255;
  public const CARD_SUB_TITLE_MAX_LENGTH = 255;
  public const CARD_DESCRIPTION_MAX_LENGTH = 1000;
}

?>