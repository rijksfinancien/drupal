<?php

namespace Drupal\minfin_search\Controller;

/**
 * Defines the 'RBV' search page.
 */
class SearchRbvController extends SearchController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'rbv';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRouteName(): string {
    return 'minfin_search.search.rbv';
  }

}
