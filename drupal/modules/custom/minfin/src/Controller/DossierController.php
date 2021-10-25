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
 * Provides an overview of dossiers.
 */
class DossierController extends ControllerBase implements FormInterface {

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
    return 'minfin_dossiers_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPage(): array {
    $build['form'] = $this->formBuilder->getForm($this);

    $header = [
      [
        'data' => $this->t('Type'),
        'field' => 'type',
        'width' => '20%',
      ],
      [
        'data' => $this->t('Year'),
        'field' => 'jaar',
        'width' => '10%',
      ],
      [
        'data' => $this->t('Phase'),
        'field' => 'fase',
        'width' => '20%',
      ],
      [
        'data' => 'Hoofdstuk id',
        'field' => 'hoofdstuk_minfin_id',
        'width' => '10%',
      ],
      [
        'data' => $this->t('Dossier number'),
        'field' => 'dossier_number',
        'width' => '20%',
      ],
      [
        'data' => $this->t('Actions'),
        'width' => '20%',
      ],
    ];

    $rows = [];
    $query = $this->connection->select('mf_kamerstuk_dossier', 'kd');
    $query = $query->fields('kd', [
      'kamerstuk_dossier_id',
      'type',
      'jaar',
      'fase',
      'hoofdstuk_minfin_id',
      'dossier_number',
    ]);
    if ($type = $this->currentRequest->get('type')) {
      $query->condition('type', $type);
    }
    if ($year = $this->currentRequest->get('jaar')) {
      $query->condition('jaar', $year);
    }
    if ($phase = $this->currentRequest->get('fase')) {
      $query->condition('fase', $phase);
    }
    if ($hoofdstukMinfinId = $this->currentRequest->get('hoofdstuk_minfin_id')) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId);
    }
    $query = $query->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
    $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(50);
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $rows[] = [
        $record['type'],
        $record['jaar'],
        $record['fase'],
        $record['hoofdstuk_minfin_id'],
        $record['dossier_number'],
        Link::createFromRoute($this->t('Edit'), 'minfin.mf_dossier.edit', [
          'dossierId' => $record['kamerstuk_dossier_id'],
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
    $types = $this->connection->select('mf_kamerstuk_dossier', 'kd')
      ->fields('kd', ['type'])
      ->orderBy('kd.type', 'ASC')
      ->execute()->fetchAllKeyed(0, 0);
    $form['type'] = [
      '#type' => 'select',
      '#options' => $types,
      '#empty_option' => $this->t('Select'),
      '#title' => $this->t('Type'),
      '#default_value' => $this->currentRequest->get('naam'),
    ];

    $form['jaar'] = [
      '#type' => 'number',
      '#title' => $this->t('Year'),
      '#min' => 1950,
      '#default_value' => $this->currentRequest->get('jaar'),
    ];

    $phases = $this->connection->select('mf_kamerstuk_dossier', 'kd')
      ->fields('kd', ['fase'])
      ->orderBy('kd.jaar', 'ASC')
      ->execute()->fetchAllKeyed(0, 0);
    $form['fase'] = [
      '#type' => 'select',
      '#options' => $phases,
      '#empty_option' => $this->t('Select'),
      '#title' => $this->t('Phase'),
      '#default_value' => $this->currentRequest->get('fase'),
    ];

    $form['hoofdstuk_minfin_id'] = [
      '#type' => 'textfield',
      '#title' => 'Hoofdstuk id',
      '#default_value' => $this->currentRequest->get('hoofdstuk_minfin_id'),
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
