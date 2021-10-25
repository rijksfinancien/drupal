<?php

namespace Drupal\minfin_search;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class to define the menu_link breadcrumb builder.
 */
class BreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * BreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The translationinterface.
   */
  public function __construct(TranslationInterface $stringTranslation) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    return (strpos($route_match->getRouteName(), 'minfin_search.search') === 0);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match): Breadcrumb {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);
    $breadcrumb->addCacheableDependency(0);

    $links = [];
    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    $explodedRouteName = explode('.', $route_match->getRouteName());
    if (isset($explodedRouteName[2])) {
      $links[] = Link::createFromRoute($this->t('Search'), 'minfin_search.search');
    }

    return $breadcrumb->setLinks($links);
  }

}
