<?php

namespace Drupal\minfin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides an overview of import logs.
 */
class ImportLogController extends ControllerBase implements FormInterface {

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a ImportLogController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FormBuilderInterface $formBuilder, DateFormatterInterface $dateFormatter, RequestStack $requestStack, Connection $connection) {
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->formBuilder = $formBuilder;
    $this->dateFormatter = $dateFormatter;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('date.formatter'),
      $container->get('request_stack'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_log';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPage(): array {
    $build['form'] = $this->formBuilder->getForm($this);

    $header = [
      [
        'data' => $this->t('Upload date'),
        'field' => 'created',
        'sort' => 'desc',
        'width' => '8%',
      ],
      [
        'data' => $this->t('Uploaded by'),
        'width' => '8%',
      ],
      [
        'data' => $this->t('Type'),
        'field' => 'type',
        'width' => '8%',
      ],
      [
        'data' => $this->t('Sub type/Phase'),
        'field' => 'sub_type',
        'width' => '8%',
      ],
      [
        'data' => $this->t('Year'),
        'field' => 'year',
        'width' => '4%',
      ],
      [
        'data' => $this->t('Name'),
        'field' => 'name',
        'width' => '20%',
      ],
      [
        'data' => $this->t('Filename'),
        'width' => '18%',
      ],
      [
        'data' => $this->t('Imported'),
        'width' => '4%',
      ],
      [
        'data' => $this->t('Skipped'),
        'width' => '4%',
      ],
      [
        'data' => $this->t('State'),
        'field' => 'state',
        'width' => '4%',
      ],
      [
        'data' => $this->t('Operations'),
        'colspan' => 2,
        'width' => '14%',
      ],
    ];

    $rows = [];
    $query = $this->connection->select('mf_log', 'l');
    $query = $query->fields('l', [
      'id',
      'created',
      'type',
      'sub_type',
      'year',
      'name',
      'fid',
      'uid',
      'rows_imported',
      'rows_skipped',
      'state',
    ]);
    if ($type = $this->currentRequest->get('type')) {
      $query->condition('type', $type);
    }
    if ($name = $this->currentRequest->get('name')) {
      $query->condition('name', '%' . $this->connection->escapeLike($name) . '%', 'LIKE');
    }
    if ($year = $this->currentRequest->get('year')) {
      $query->condition('year', $year);
    }
    if ($subType = $this->currentRequest->get('sub_type')) {
      $query->condition('sub_type', $subType);
    }
    if ($state = $this->currentRequest->get('state')) {
      $query->condition('state', $state);
    }
    $query = $query->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
    $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(25);
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      /** @var \Drupal\file\Entity\File $file */
      $file = $this->fileStorage->load($record['fid']);
      /** @var \Drupal\user\Entity\User $user */
      $user = $this->userStorage->load($record['uid']);

      $countQuery = $this->connection->select('mf_log_message', 'lm');
      $countQuery->addExpression('COUNT(*)');
      $countQuery->condition('mf_log_id', $record['id'], '=');
      $logMessages = $countQuery->execute()->fetchField();

      $rows[] = [
        $this->dateFormatter->format($record['created']),
        ($user ? $user->getDisplayName() : ''),
        $record['type'],
        $record['sub_type'],
        $record['year'],
        $record['name'],
        ($file ? Link::fromTextAndUrl($file->getFilename(), Url::fromUri(file_create_url($file->getFileUri()))) : ''),
        $record['rows_imported'],
        $record['rows_skipped'],
        $this->getState($record['state']),
        ($logMessages ? Link::createFromRoute($this->t('View the log'), 'minfin.import_log.messages', ['logId' => $record['id']]) : '-'),
        Link::createFromRoute($this->t('Delete'), 'minfin.import_log.delete', ['logId' => $record['id']]),
      ];
    }

    $build['import_log'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No import logs found.'),
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
    $types = $this->connection->select('mf_log', 'l')
      ->distinct(TRUE)
      ->fields('l', ['type'])
      ->orderBy('type', 'asc')
      ->execute()->fetchAllKeyed(0, 0);

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $types ?? [],
      '#empty_option' => $this->t('- Select type -'),
      '#default_value' => $this->currentRequest->get('type'),
    ];

    $form['state'] = [
      '#type' => 'select',
      '#title' => $this->t('State'),
      '#options' => [
        0 => $this->getState(0),
        1 => $this->getState(1),
        2 => $this->getState(2),
      ],
      '#empty_option' => $this->t('- Select state -'),
      '#default_value' => $this->currentRequest->get('state'),
    ];

    $form['year'] = [
      '#type' => 'number',
      '#title' => $this->t('Year'),
      '#min' => 1950,
      '#default_value' => $this->currentRequest->get('year'),
    ];

    $form['phase'] = [
      '#type' => 'select',
      '#title' => $this->t('Sub type/Phase'),
      '#options' => [
        'owb' => $this->t('OWB'),
        'supp1' => $this->t('1e suppletoire'),
        'supp2' => $this->t('2e suppletoire'),
        'jv' => $this->t('JV'),
      ],
      '#empty_option' => $this->t('- Select value -'),
      '#default_value' => $this->currentRequest->get('sub_type'),
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $this->currentRequest->get('name'),
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

  /**
   * Return the state name.
   *
   * @param int $state
   *   The state.
   *
   * @return string
   *   The state name.
   */
  private function getState(int $state): string {
    $states = [
      0 => $this->t('Importing'),
      1 => $this->t('Imported'),
      2 => $this->t('Import failed'),
    ];

    return $states[$state] ?? '';
  }

}
