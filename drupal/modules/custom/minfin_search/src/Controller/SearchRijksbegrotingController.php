<?php

namespace Drupal\minfin_search\Controller;

/**
 * Defines the 'Rijksbegroting' search page.
 */
class SearchRijksbegrotingController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'rijksbegroting';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'minfin_search.search.rijksbegroting';
  }

}
