<?php

namespace Drupal\minfin_solr;

/**
 * Defines the inferface for the SOLR client service.
 */
interface SolrClientInterface {

  /**
   * Retrieve a list of errors.
   *
   * @return array
   *   The errors.
   */
  public function getErrors(): array;

  /**
   * Set the SOLR core.
   *
   * @param string $core
   *   The SOLR core.
   */
  public function setCore(string $core): void;

  /**
   * Preform a search.
   *
   * @param int $page
   *   The active page.
   * @param int $recordsPerPage
   *   Records shown per page.
   * @param string|null $search
   *   The search value.
   * @param string|null $sort
   *   The sort value.
   * @param string $type
   *   The type of data to search for.
   * @param array $activeFacets
   *   The active facets.
   *
   * @return array
   *   An array containing the search results.
   */
  public function search(int $page, int $recordsPerPage, ?string $search, ?string $sort, string $type = 'all', array $activeFacets = []): array;

  /**
   * Get the suggestions.
   *
   * @param string $search
   *   The search value.
   *
   * @return array
   *   An array with suggestions.
   */
  public function getSuggestions(string $search): array;

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
  public function update(array $data, bool $commit = TRUE);

  /**
   * Delete the Solr index.
   *
   * @param string $id
   *   The id.
   *
   * @return mixed|false
   *   The response.
   */
  public function delete(string $id);

  /**
   * Delete the Solr index.
   *
   * @param array $query
   *   The delete query as array.
   *
   * @return mixed|false
   *   The response.
   */
  public function deleteQuery(array $query);

}
