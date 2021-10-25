<?php

namespace Drupal\minfin_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\minfin_search\SearchUrlTrait;

/**
 * Defines the form that processes the sorting filter.
 */
class SearchSortingFilterForm extends FormBase {

  use SearchUrlTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_search_sorting_filter_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $routeName
   *   The route name of the search page.
   * @param int $recordsPerPage
   *   Records shown per page.
   * @param string|null $search
   *   The search value.
   * @param string|null $sort
   *   The sort value.
   * @param array $activeFacets
   *   The active facets.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $routeName = 'minfin_search.search', int $recordsPerPage = 10, ?string $search = NULL, ?string $sort = NULL, array $activeFacets = []): array {
    $form_state->set('routeName', $routeName);
    $form_state->set('recordsPerPage', $recordsPerPage);
    $form_state->set('search', $search);
    $form_state->set('activeFacets', $activeFacets);

    $form['sorting'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort on'),
      '#title_display' => 'invisible',
      '#options' => [
        'score desc' => $this->t('on relevance'),
        'fiscal_year desc' => $this->t('on fiscal year'),
        'fiscal_year asc' => $this->t('on fiscal year ascending'),
      ],
      '#default_value' => $sort ?? 'score desc',
      '#attributes' => [
        'title' => $this->t('Select the order in which the results will be sorted'),
        'class' => ['chosen', 'tall'],
        'data-disable-search' => 'disable_search',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Refresh'),
      '#prefix' => Markup::create('<noscript>'),
      '#suffix' => Markup::create('</noscript>'),
    ];

    $form['#attached']['library'][] = 'minfin_search/search_sorting_filter';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($routeName = $form_state->get('routeName')) {
      $url = $this->buildSearchUrl($routeName, 1, $form_state->get('recordsPerPage'), $form_state->get('search'), $form_state->getValue('sorting'), $form_state->get('activeFacets'));
      $form_state->setRedirectUrl($url);
    }
  }

}
