<?php

namespace Drupal\minfin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides an overview of kamerstuk ISB titles.
 */
class KamerstukIsbTitleController extends ControllerBase implements FormInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a KamerstukIsbTitleController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(FormBuilderInterface $formBuilder, Connection $connection, RequestStack $requestStack) {
    $this->formBuilder = $formBuilder;
    $this->connection = $connection;
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('database'),
      $container->get('request_stack'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_kamerstuk_isb_title_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPage(): array {
    $options = ['attributes' => ['class' => ['button', 'button--primary']]];
    $build['action_links'] = [
      '#type' => 'inline_template',
      '#template' => '<ul class="action-links"><li>{{ link }}</li></ul>',
      '#context' => ['link' => Link::createFromRoute($this->t('Add title'), 'minfin.kamerstuk_isb_title.create', [], $options)],
    ];

    $build['form'] = $this->formBuilder->getForm($this);

    $header = [
      [
        'data' => $this->t('Year'),
        'field' => 'jaar',
        'width' => '10%',
      ],
      [
        'data' => $this->t('Phase'),
        'field' => 'fase',
        'width' => '10%',
      ],
      [
        'data' => $this->t('Hoofstuk'),
        'field' => 'hoofdstuk_minfin_id',
        'width' => '10%',
      ],
      [
        'data' => $this->t('Name'),
        'field' => 'naam',
        'width' => '30%',
      ],
      [
        'data' => $this->t('Date'),
        'field' => 'date',
        'width' => '20%',
      ],
      [
        'data' => $this->t('Actions'),
        'width' => '20%',
      ],
    ];

    $rows = [];
    $query = $this->connection->select('mf_kamerstuk_isb_title', 'kit');
    $query = $query->fields('kit', [
      'jaar',
      'fase',
      'hoofdstuk_minfin_id',
      'naam',
      'date',
    ]);
    if ($year = $this->currentRequest->get('jaar')) {
      $query->condition('jaar', $year);
    }
    if ($phase = $this->currentRequest->get('fase')) {
      $query->condition('fase', $phase);
    }
    if ($hoofdstukMinfinId = $this->currentRequest->get('hoofdstuk_minfin_id')) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId);
    }
    if ($name = $this->currentRequest->get('naam')) {
      $query->condition('naam', '%' . $this->connection->escapeLike($name) . '%', 'LIKE');
    }
    $query = $query->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
    $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(50);
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $rows[] = [
        $record['jaar'],
        $record['fase'],
        $record['hoofdstuk_minfin_id'],
        $record['naam'],
        ($record['date'] ? date('d-m-Y', strtotime($record['date'])) : ''),
        Link::createFromRoute($this->t('Delete'), 'minfin.kamerstuk_isb_title.delete', [
          'jaar' => $record['jaar'],
          'fase' => $record['fase'],
          'hoofdstukMinfinId' => $record['hoofdstuk_minfin_id'],
        ]),
      ];
    }

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No records found.'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['jaar'] = [
      '#type' => 'number',
      '#title' => $this->t('Year'),
      '#min' => 1950,
      '#default_value' => $this->currentRequest->get('jaar'),
    ];

    $form['fase'] = [
      '#type' => 'number',
      '#title' => $this->t('Phase'),
      '#min' => 0,
      '#default_value' => $this->currentRequest->get('fase'),
    ];

    $form['hoofdstuk_minfin_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hoofdstuk id'),
      '#default_value' => $this->currentRequest->get('hoofdstuk_minfin_id'),
    ];

    $form['naam'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $this->currentRequest->get('naam'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // No validation.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $form_state->setRedirect('<current>', array_filter($form_state->getValues(), static function ($value) {
      return $value !== NULL && $value !== '';
    }));
  }

}
