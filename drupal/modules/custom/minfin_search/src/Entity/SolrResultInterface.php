<?php

namespace Drupal\minfin_search\Entity;

/**
 * Defines a SOLR result interface.
 */
interface SolrResultInterface {

  /**
   * Get the title.
   *
   * @return string
   *   The title.
   */
  public function getTitle(): string;

  /**
   * Get the years.
   *
   * @return array
   *   A list with years.
   */
  public function getYears(): array;

  /**
   * Get the document types.
   *
   * @return array
   *   A list with document types.
   */
  public function getDocumentTypes(): array;

  /**
   * Get the phases.
   *
   * @return array
   *   A list with phases.
   */
  public function getPhases(): array;

  /**
   * Get the content type.
   *
   * @return string
   *   The content type.
   */
  public function getType(): string;

  /**
   * Get the URL.
   *
   * @param array $selectedValues
   *   An array with the current state of the select options.
   *
   * @return string
   *   A valid URL.
   */
  public function getUrl(array $selectedValues = []): string;

}
