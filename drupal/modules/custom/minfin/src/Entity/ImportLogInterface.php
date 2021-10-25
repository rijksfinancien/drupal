<?php

namespace Drupal\minfin\Entity;

/**
 * Provides an interface defining a import log entity.
 */
interface ImportLogInterface {

  /**
   * Gets the timestamp of the entity creation.
   *
   * @return int
   *   The timestamp of the entity creation operation.
   */
  public function getCreatedTime(): int;

  /**
   * Retrieve the type of the import log.
   *
   * @return string
   *   The import type.
   */
  public function getType(): string;

  /**
   * Retrieve the name of the import.
   *
   * @return string
   *   The import name.
   */
  public function getName(): string;

  /**
   * Retrieve the year of the import file.
   *
   * @return int
   *   The import file year.
   */
  public function getYear(): int;

  /**
   * Retrieve the phase of the import file.
   *
   * @return string
   *   The import file phase.
   */
  public function getPhase(): string;

  /**
   * Retrieve the name of the uploaded file.
   *
   * @return string|null
   *   The filename of the uploaded file.
   */
  public function getFilename(): ?string;

  /**
   * Retrieve the amount of imported records during the import.
   *
   * @return int
   *   The amount of imported records.
   */
  public function getImported(): int;

  /**
   * Retrieve the amount of deleted records during the import.
   *
   * @return int
   *   The amount of deleted records.
   */
  public function getDeleted(): int;

  /**
   * Increases the amount of imported records.
   *
   * @param int $amount
   *   The amount.
   *
   * @return int
   *   The total calculed imported records.
   */
  public function increaseImported(int $amount = 1): int;

  /**
   * Increases the amount of deleted records.
   *
   * @param int $amount
   *   The amount.
   *
   * @return int
   *   The total calculed deleted records.
   */
  public function increaseDeleted(int $amount = 1): int;

  /**
   * Log an error.
   *
   * @param \Drupal\minfin\Entity\ImportErrorInterface $importError
   *   The import errror to be logged.
   */
  public function logError(ImportErrorInterface $importError);

  /**
   * Get all reported errors.
   *
   * @return \Drupal\minfin\Entity\ImportError[]
   *   An array with referenced entities.
   */
  public function getLogErrors(): array;

}
