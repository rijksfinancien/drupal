<?php

namespace Drupal\minfin_search\Controller;

/**
 * Defines the 'Open data' search page.
 */
class SearchOpenDataController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'open_data';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'minfin_search.search.open_data';
  }

}
