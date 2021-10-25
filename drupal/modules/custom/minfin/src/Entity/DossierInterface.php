<?php

namespace Drupal\minfin\Entity;

/**
 * Provides an interface defining a dossier entity.
 */
interface DossierInterface {

  /**
   * Gets the timestamp of the entity creation.
   *
   * @return int
   *   The timestamp of the entity creation operation.
   */
  public function getCreatedTime(): int;

  /**
   * Retrieve the year of the dossier.
   *
   * @return int
   *   The year.
   */
  public function getYear(): int;

  /**
   * Retrieve the name of the import.
   *
   * @return string
   *   The phase.
   */
  public function getPhase(): string;

  /**
   * Retrieve the code of the dossier.
   *
   * @return string
   *   The code.
   */
  public function getCode(): string;

}
