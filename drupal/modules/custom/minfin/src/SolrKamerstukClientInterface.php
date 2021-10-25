<?php

namespace Drupal\minfin;

/**
 * Defines the inferface for the SOLR kamerstuk client service.
 */
interface SolrKamerstukClientInterface {

  /**
   * Retrieve a list of errors.
   *
   * @return array
   *   The errors.
   */
  public function getErrors(): array;

  /**
   * Updates the Solr index.
   *
   * @param bool $appendix
   *   If this kamerstuk is an appendix or not.
   * @param string $type
   *   The type.
   * @param string $phase
   *   The phase.
   * @param int $year
   *   The year.
   * @param string $name
   *   The name.
   * @param string $html
   *   The HTML.
   * @param string $anchor
   *   The anchor id.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   The artikel minfin id.
   *
   * @return mixed|false
   *   The response.
   */
  public function update(bool $appendix, string $type, string $phase, int $year, string $name, string $html, string $anchor, ?string $hoofdstukMinfinId, ?string $artikelMinfinId);

  /**
   * Delete a specific kamerstuk from the Solr index.
   *
   * @param string $type
   *   The type.
   * @param string $phase
   *   The phase.
   * @param int $year
   *   The year.
   * @param string $anchor
   *   The anchor id.
   * @param string|null $hoofdstukMinfinId
   *   The chapter minfin id.
   *
   * @return mixed|false
   *   The response.
   */
  public function delete(string $type, string $phase, int $year, string $anchor, ?string $hoofdstukMinfinId);

  /**
   * Delete all kamerstukken from the Solr index.
   *
   * @return mixed|false
   *   The response.
   */
  public function deleteAll();

}
