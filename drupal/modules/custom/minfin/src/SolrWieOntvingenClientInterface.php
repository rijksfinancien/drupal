<?php

namespace Drupal\minfin;

/**
 * Defines the inferface for the Wie Ontvingen kamerstuk client service.
 */
interface SolrWieOntvingenClientInterface {

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
   * @param array $data
   *   The data.
   * @param bool $commit
   *   Add the commit string to the reqeust.
   *
   * @return mixed|false
   *   The response.
   */
  public function update(array $data, bool $commit = FALSE);

  /**
   * Delete all indexes from the Solr index.
   *
   * @param int $year
   *   The year.
   *
   * @return mixed|false
   *   The response.
   */
  public function deleteAll(int $year);

}
