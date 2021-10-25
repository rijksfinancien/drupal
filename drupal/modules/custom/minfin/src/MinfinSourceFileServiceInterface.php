<?php

namespace Drupal\minfin;

use Drupal\file\Entity\File;

/**
 * Interface for common minfin related functions.
 */
interface MinfinSourceFileServiceInterface {

  /**
   * Get the last source file for the given values.
   *
   * @param string $importType
   *   The import type.
   * @param string|null $subType
   *   The sub type.
   * @param int|null $year
   *   The year.
   *
   * @return \Drupal\file\Entity\File|null
   *   The source file or null if not found.
   */
  public function getLastSourceFile(string $importType, ?string $subType = NULL, ?int $year = NULL): ?File;

}
