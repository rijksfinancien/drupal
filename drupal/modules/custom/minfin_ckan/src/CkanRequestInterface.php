<?php

namespace Drupal\minfin_ckan;

/**
 * Interface for the ckan request.
 */
interface CkanRequestInterface {

  /**
   * Retreive all datasets.
   *
   * @param string $title
   *   (optional) The title to retreive datasets for.
   * @param bool $keyToLower
   *   (optional) Should the key be run through strtolower.
   *
   * @return array
   *   An array with the search results.
   */
  public function getDatasets($title = '', $keyToLower = FALSE): array;

}
