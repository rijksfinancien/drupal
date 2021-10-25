<?php

namespace Drupal\minfin;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Interface for common minfin related functions.
 */
interface MinfinServiceInterface {

  /**
   * Get the last imported year.
   *
   * @return int
   *   The last imported year.
   */
  public function getLastYear(): int;

  /**
   * Get the first imported year.
   *
   * @return int
   *   The first imported year.
   */
  public function getFirstYear(): int;

  /**
   * Get the active year.
   *
   * @return int
   *   The active year.
   */
  public function getActiveYear(): int;

  /**
   * Return a list of chapters for the given year.
   *
   * @param int|null $year
   *   The year to get the data for. Defaults to the active year.
   *
   * @return string[]
   *   An array with all chapters.
   */
  public function getChaptersForYear($year = NULL): array;

  /**
   * Return a list with available years.
   *
   * @return int[]
   *   An array with all available years.
   */
  public function getAvailableYears(): array;

  /**
   * Get the artikel minfin id from the given kamerstuk params.
   *
   * @param string $type
   *   The kamerstuk type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $anchor
   *   The anchor id.
   *
   * @return string|null
   *   The artikel minfin id..
   */
  public function getArtikelMinfinIdFromKamerstukAnchor(string $type, int $year, string $phase, ?string $hoofdstukMinfinId, string $anchor): ?string;

  /**
   * Build the correct URL for a kamerstuk.
   *
   * @param string $routePrefix
   *   The route prefix.
   * @param int $year
   *   The year.
   * @param array $options
   *   The url options.
   * @param string|null $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   The artikel minfin id.
   *
   * @return \Drupal\Core\Url|null
   *   The URL to the kamerstuk.
   */
  public function buildKamerstukUrl(string $routePrefix, int $year, array $options = [], ?string $phase = NULL, ?string $hoofdstukMinfinId = NULL, ?string $artikelMinfinId = NULL): ?Url;

  /**
   * Build the correct URL for a kamerstuk pdf.
   *
   * @param string $routePrefix
   *   The route prefix.
   * @param int $year
   *   The year.
   * @param array $options
   *   The url options.
   * @param string|null $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   *
   * @return \Drupal\Core\Url|null
   *   The URL to the kamerstuk pdf.
   */
  public function buildKamerstuPdfkUrl(string $routePrefix, int $year, array $options = [], ?string $phase = NULL, ?string $hoofdstukMinfinId = NULL): ?Url;

  /**
   * Get a categories list with all phases.
   *
   * @return array
   *   A categories list with all phases.
   */
  public function getCategories(): array;

  /**
   * Gets the chapter url.
   *
   * @param string $chapterId
   *   The chapter id.
   * @param string $year
   *   The year.
   * @param string $documentType
   *   The document type.
   *
   * @return \Drupal\Core\Url
   *   The chapter url.
   */
  public function getChapterUrl(string $chapterId, string $year, string $documentType = ''): Url;

  /**
   * Builds the chapter link.
   *
   * @param string $title
   *   The title.
   * @param string $chapterId
   *   The chapter id.
   * @param string $year
   *   The year.
   * @param string $documentType
   *   The document type.
   *
   * @return \Drupal\Core\Link
   *   The link.
   */
  public function buildChapterLink(string $title, string $chapterId, string $year, string $documentType = ''): Link;

  /**
   * Get the phase by document type.
   *
   * @param string $documentType
   *   The document type.
   *
   * @return string|null
   *   The phase.
   */
  public function getPhaseByDocumentType(string $documentType): ?string;

  /**
   * Get the kamsteruktype by document type.
   *
   * @param string $documentType
   *   The document type.
   *
   * @return string|null
   *   The kamsteruktype.
   */
  public function getTypeByDocumentType(string $documentType): ?string;

  /**
   * Get an URL to the most recent uploaded kamerstuk.
   *
   * @param array $options
   *   The url options.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   The artikel minfin id.
   *
   * @return \Drupal\Core\Url|null
   *   The URL to the kamerstuk.
   */
  public function getMostRecentKamerstukUrl(array $options = [], ?string $hoofdstukMinfinId = NULL, ?string $artikelMinfinId = NULL): ?Url;

}
