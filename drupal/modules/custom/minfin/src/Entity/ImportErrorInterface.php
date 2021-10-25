<?php

namespace Drupal\minfin\Entity;

/**
 * Provides an interface defining a import error entity.
 */
interface ImportErrorInterface {

  /**
   * An error occured which is definetly not correct.
   *
   * For example a required field was empty, or a column that should contain a
   * number contained a letter instead.
   */
  public const SEVERITY_ERROR = 1;

  /**
   * Something odd was noticed during the import and we assume its not correct.
   */
  public const SEVERITY_WARNING = 2;

  /**
   * An error occured during the import, but we could automatically fix it.
   *
   * For example the "artikel nummer" which should be an integer was written as
   * a decimal, in which case the decimal part can be removed as it usually
   * indicates a "artikel onderverdeling". Or the column containing the amount
   * was written with a euro sign, in which case we stripped the value of it.
   */
  public const SEVERITY_CHANGED = 3;

  /**
   * A line that was not imported because the logic told us.
   */
  public const SEVERITY_SKIPPED = 4;

  /**
   * Gets the timestamp of the entity creation.
   *
   * @return int
   *   The timestamp of the entity creation operation.
   */
  public function getCreatedTime(): int;

  /**
   * Retrieve the severity of the import error.
   *
   * @return int
   *   The severity.
   */
  public function getSeverity(): int;

  /**
   * Retrieve the severity name of the import error.
   *
   * @return string
   *   The severity name.
   */
  public function getSeverityName(): string;

  /**
   * Retrieve the message of the import error.
   *
   * @return string
   *   The message.
   */
  public function getMessage(): string;

  /**
   * Retrieve the line number on which the error occured.
   *
   * @return int|null
   *   The line number if known.
   */
  public function getLineNumber(): ?int;

}
