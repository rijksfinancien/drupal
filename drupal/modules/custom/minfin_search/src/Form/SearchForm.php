<?php

namespace Drupal\minfin_search\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\minfin_search\Controller\SearchController;
use Drupal\minfin_search\SearchUrlTrait;

/**
 * Defines the searchform.
 */
class SearchForm extends AdvancedSearchForm {

  use SearchUrlTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $queryParams = $this->currentRequest->query->all();
    $routeName = $this->routeMatch->getRouteName();

    $search = $queryParams['search'] ?? NULL;
    unset($queryParams['search']);

    $sort = $queryParams['sort'] ?? NULL;
    unset($queryParams['sort']);

    $facets = [];
    $activeFacets = [];
    foreach ($queryParams as $k => $v) {
      if (!is_array($v)) {
        $v = [$v];
      }
      $activeFacets[$k] = $v;
    }
    foreach ($activeFacets as $k => $values) {
      foreach ($values as $delta => $v) {
        $tmpFilters = $activeFacets;
        unset($tmpFilters[$k][$delta]);
        $facets[] = [
          '#type' => 'link',
          '#title' => SearchController::FACET_LABELS[$k] . ': ' . $v,
          '#url' => $this->buildSearchUrl($routeName, 1, 10, $search, $sort, $tmpFilters),
          '#attributes' => [
            'class' => ['cross-after'],
          ],
        ];
      }
    }

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container'],
      ],
    ];

    $form['wrapper']['search_term'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
      '#default_value' => $search,
      '#attributes' => [
        'placeholder' => $this->t('Enter your search term'),
      ],
    ];

    $form['wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    $form['wrapper']['facet_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['facet-wrapper'],
      ],
    ];

    $form['wrapper']['facet_wrapper'][] = $facets;

    return $form;
  }

}
