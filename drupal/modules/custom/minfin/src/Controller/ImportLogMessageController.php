<?php

namespace Drupal\minfin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides an overview of import log messages.
 */
class ImportLogMessageController extends ControllerBase implements FormInterface {

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
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(FormBuilderInterface $formBuilder, RequestStack $requestStack, Connection $connection) {
    $this->formBuilder = $formBuilder;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('request_stack'),
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_error_log';
  }

  /**
   * Create a table with all import errors.
   *
   * @param string $logId
   *   The log id.
   *
   * @return array
   *   A Drupal render array.
   */
  public function buildPage(string $logId): array {
    $build['form'] = $this->formBuilder->getForm($this);

    $header = [
      [
        'data' => $this->t('Linenumber'),
        'field' => 'line',
        'sort' => 'desc',
        'width' => '10%',
      ],
      [
        'data' => $this->t('Log message'),
        'field' => 'message_value',
        'width' => '70%',
      ],
      [
        'data' => $this->t('Severity'),
        'field' => 'severity',
        'width' => '20%',
      ],
    ];

    $rows = [];
    $query = $this->connection->select('mf_log_message', 'lm');
    $query = $query->fields('lm', [
      'line',
      'severity',
      'message',
      'variables',
    ]);
    $query->condition('mf_log_id', $logId, '=');
    if ($severities = $this->currentRequest->get('severity')) {
      $query->condition('severity', $severities, 'IN');
    }
    $query = $query->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
    $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(25);
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $rows[] = [
        $record['line'],
        // phpcs:disable
        check_markup($this->t($record['message'], unserialize($record['variables'])), 'safe_html'),
        $this->getSeverityName((int) $record['severity']),
      ];
    }

    $build['import_log'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No import errors found.'),
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
    $form['severity'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Show errors with the following severity'),
      '#options' => [
        1 => $this->getSeverityName(1),
        2 => $this->getSeverityName(2),
        3 => $this->getSeverityName(3),
        4 => $this->getSeverityName(4),
      ],
      '#required' => TRUE,
      '#default_value' => $this->currentRequest->get('severity') ?? [
        1,
        2,
        3,
        4,
      ],
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
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
   * Retrieve the severity name of the import error.
   *
   * @param int $severity
   *   The severity.
   *
   * @return string
   *   The severity name.
   */
  private function getSeverityName(int $severity): string {
    $severityName = [
      1 => $this->t('Error'),
      2 => $this->t('Warning'),
      3 => $this->t('Changed'),
      4 => $this->t('Skipped'),
    ];

    return $severityName[$severity] ?? '';
  }

}
