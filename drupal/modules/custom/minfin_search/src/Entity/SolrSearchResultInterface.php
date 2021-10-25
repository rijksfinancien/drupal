<?php

namespace Drupal\minfin_search\Entity;

/**
 * Defines a SOLR search result interface.
 */
interface SolrSearchResultInterface extends SolrResultInterface {

  /**
   * Get a summary of the description.
   *
   * @return string|\Drupal\Component\Render\MarkupInterface
   *   The summary.
   */
  public function getSummary();

  /**
   * Get a list of quick links.
   *
   * @return \Drupal\Core\Link[]
   *   A list of quick links.
   */
  public function getQuickLinks(): array;

}
