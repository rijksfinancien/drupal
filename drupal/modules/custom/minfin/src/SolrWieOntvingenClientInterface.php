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
   *
   * @return mixed|false
   *   The response.
   */
  public function update(array $data);

  /**
   * Delete all indexes from the Solr index.
   *
   * @return mixed|false
   *   The response.
   */
  public function deleteAll();

}
