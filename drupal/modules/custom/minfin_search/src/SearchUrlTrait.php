<?php

namespace Drupal\minfin_search;

use Drupal\Core\Url;

/**
 * Add a helper function to build the search url.
 */
trait SearchUrlTrait {

  /**
   * Build the search URL.
   *
   * @param string $routeName
   *   The route name of the search page.
   * @param int $page
   *   The active page.
   * @param int $recordsPerPage
   *   Records shown per page.
   * @param string|null $search
   *   The search value.
   * @param string|null $sort
   *   The sort value.
   * @param array $facets
   *   The facets.
   * @param array $options
   *   URL options.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  protected function buildSearchUrl(string $routeName, int $page, int $recordsPerPage, ?string $search = NULL, ?string $sort = NULL, array $facets = [], array $options = []): Url {
    $options['query'] = array_filter($facets);
    if ($search) {
      $options['query']['search'] = $search;
    }
    if ($sort) {
      $options['query']['sort'] = $sort;
    }

    $params = [
      'page' => $page,
      'recordsPerPage' => $recordsPerPage,
    ];

    return Url::fromRoute($routeName, $params, $options);
  }

}
