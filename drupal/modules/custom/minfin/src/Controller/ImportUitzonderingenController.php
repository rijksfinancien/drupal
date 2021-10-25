<?php

namespace Drupal\minfin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides an overview of import uitzonderingen.
 */
class ImportUitzonderingenController extends ControllerBase {

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
   * Constructs an ImportUitzonderingenController object.
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
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildPage(): array {
    $header = [
      [
        'data' => $this->t('Id'),
        'width' => '5%',
      ],
      [
        'data' => $this->t('Type'),
        'width' => '9%',
      ],
      [
        'data' => $this->t('Year'),
        'width' => '8%',
      ],
      [
        'data' => $this->t('Phase'),
        'width' => '8%',
      ],
      [
        'data' => $this->t('Chapter'),
        'width' => '9%',
      ],
      [
        'data' => $this->t('Level 1'),
        'width' => '8%',
      ],
      [
        'data' => $this->t('Level 2'),
        'width' => '8%',
      ],
      [
        'data' => $this->t('Level 3'),
        'width' => '8%',
      ],
      [
        'data' => $this->t('Article chapter'),
        'width' => '9%',
      ],
      [
        'data' => $this->t('Article number'),
        'width' => '9%',
      ],
      [
        'data' => $this->t('B tabel'),
        'width' => '9%',
      ],
      [
        'data' => $this->t('No subchapters'),
        'width' => '10%',
      ],
    ];

    // Load import uitzonderingen.
    $query = $this->connection->select('mf_uitzonderingen', 'mf_uitzonderingen');
    $query->fields('mf_uitzonderingen');
    if ($result = $query->execute()) {
      while ($record = $result->fetchAssoc()) {
        $rows[] = [
          $record['id'] ?? '',
          $record['type'] ?? '',
          $record['jaar'] ?? '',
          $record['fase'] ?? '',
          $record['hoofdstuk_minfin_id'] ?? '',
          $record['level_1'] ?? '',
          $record['level_2'] ?? '',
          $record['level_3'] ?? '',
          $record['hoofdstuk_alternatief_id'] ?? '',
          $record['artikel_minfin_id'] ?? '',
          $record['b_tabel'] ?? '',
          $record['geen_subhoofdstukken'] ?? 0,
        ];
      }
    }

    $build['exceptions'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No import exceptions found.'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

}
