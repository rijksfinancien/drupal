<?php

namespace Drupal\minfin_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the advanced searchform.
 */
class AdvancedSearchForm extends FormBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * The Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * AdvancedSearchForm constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   The current route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   */
  public function __construct(CurrentRouteMatch $routeMatch, RequestStack $requestStack, MinfinServiceInterface $minfinService) {
    $this->routeMatch = $routeMatch;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->minfinService = $minfinService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('request_stack'),
      $container->get('minfin.minfin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_advanced_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['advanced-search-container', 'container'],
      ],
    ];

    $form['wrapper']['inner_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['advanced-search-inner-wrapper'],
      ],
    ];

    $form['wrapper']['inner_wrapper']['row1'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['search-row'],
      ],
    ];

    $form['wrapper']['inner_wrapper']['row1']['search_term'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
      '#default_value' => $this->currentRequest->get('search'),
      '#attributes' => [
        'placeholder' => $this->t('Enter your search term'),
        'class' => ['js-suggester'],
        'autocomplete' => 'off',
      ],
    ];

    $form['wrapper']['inner_wrapper']['row1']['year'] = [
      '#type' => 'select',
      '#title' => $this->t('Year'),
      '#options' => $this->minfinService->getAvailableYears(),
      '#empty_option' => $this->t('Year'),
      '#title_display' => 'invisible',
    ];

    $documentTypes = [
      '1e suppletoire' => '1e suppletoire',
      '2e suppletoire' => '2e suppletoire',
      'Begroting' => 'Begroting',
      'Financieel jaarverslag' => 'Financieel jaarverslag',
      'Jaarverslag' => 'Jaarverslag',
      'Miljoenennota' => 'Miljoenennota',
      'Najaarsnota' => 'Najaarsnota',
      'Slotwet' => 'Slotwet',
      'Voorjaarsnota' => 'Voorjaarsnota',
    ];
    $form['wrapper']['inner_wrapper']['row1']['document_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Document type(s)'),
      '#options' => $documentTypes,
      '#empty_option' => $this->t('Choose document type(s)'),
      '#title_display' => 'invisible',
    ];

    $form['wrapper']['inner_wrapper']['row1']['submit-wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form-item', 'form-item-submit-wrapper'],
      ],
    ];

    $form['wrapper']['inner_wrapper']['row1']['submit-wrapper']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    $form['wrapper']['inner_wrapper']['row2'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['search-row'],
      ],
    ];

    $contentTypes = [
      'all' => $this->t('Complete site'),
      'rijksbegroting' => 'Rijksbegroting',
      'rbv' => 'RBV',
      'open_data' => 'Open data',
    ];
    $form['wrapper']['inner_wrapper']['row2']['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Search in:'),
      '#required' => TRUE,
      '#options' => $contentTypes,
      '#default_value' => 'all',
    ];

    $form['wrapper']['inner_wrapper']['row3'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['search-row'],
      ],
    ];

    $form['search_suggestions_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['search-suggestions-outer-wrapper', 'container'],
      ],
    ];

    $form['search_suggestions_wrapper']['search_suggestions'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['search-suggestions-wrapper'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $options = [];
    if ($search = $form_state->getValue('search_term')) {
      $options['query']['search'] = $search;
    }

    if ($year = $form_state->getValue('year')) {
      $options['query']['facet_fiscal_year'] = $year;
    }

    if ($documentType = $form_state->getValue('document_type')) {
      $options['query']['facet_document_type'] = $documentType;
    }

    $routeName = 'minfin_search.search';
    if (($type = $form_state->getValue('type')) && $type !== 'all') {
      $routeName = 'minfin_search.search.' . $type;
    }

    $form_state->setRedirect($routeName, [], $options);
  }

}
