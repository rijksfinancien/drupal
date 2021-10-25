<?php

namespace Drupal\minfin\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for the chapter names.
 */
class ChapterNamesForm extends FormBase {

  /**
   * The connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * Constructor.
   *
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   */
  public function __construct(MinfinServiceInterface $minfinService, Connection $connection) {
    $this->minfinService = $minfinService;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('minfin.minfin'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_chapter_names_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'minfin/chapter_names_form';

    $form['values'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $years = $this->minfinService->getAvailableYears();
    foreach ($years as $year) {
      $form['values'][$year] = [
        '#type' => 'details',
        '#title' => $year,
        '#open' => FALSE,
      ];

      $query = $this->connection->select('mf_hoofdstuk', 'h');
      $query->leftJoin('mf_b_tabel', 'b', 'b.hoofdstuk_id = h.hoofdstuk_id AND b.jaar = h.jaar');
      $query->fields('h', ['hoofdstuk_id', 'hoofdstuk_minfin_id', 'naam']);
      $query->addField('b', 'jaar', 'btabel');
      $query->condition('h.jaar', $year, '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $form['values'][$year]['data'][$record['hoofdstuk_id']] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['form-row'],
          ],
        ];

        $form['values'][$year]['data'][$record['hoofdstuk_id']]['id'] = [
          '#type' => 'textfield',
          '#title' => 'id',
          '#title_display' => 'invisible',
          '#default_value' => $record['hoofdstuk_minfin_id'],
          '#maxlength' => 8,
          '#size' => 8,
          '#disabled' => (bool) $record['btabel'],
        ];

        $form['values'][$year]['data'][$record['hoofdstuk_id']]['name'] = [
          '#type' => 'textfield',
          '#title' => 'name',
          '#title_display' => 'invisible',
          '#default_value' => $record['naam'],
          '#maxlength' => 255,
        ];
      }

      // Add an empty option so we can add additional values.
      $form['values'][$year]['data'][0] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['form-row'],
        ],
      ];

      $form['values'][$year]['data'][0]['id'] = [
        '#type' => 'textfield',
        '#title' => 'id',
        '#title_display' => 'invisible',
        '#maxlength' => 8,
        '#size' => 8,
      ];

      $form['values'][$year]['data'][0]['name'] = [
        '#type' => 'textfield',
        '#title' => 'name',
        '#title_display' => 'invisible',
        '#maxlength' => 255,
      ];

      $form['values'][$year]['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save data for @year', ['@year' => $year]),
      ];
    }

    $form['copy'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Copy values'),
      '#tree' => TRUE,
      '#attributes' => [
        'class' => ['form-row'],
      ],
    ];

    $form['copy']['text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Copy values from'),
      '#attributes' => [
        'class' => ['form-item'],
      ],
    ];

    $form['copy']['from'] = [
      '#type' => 'number',
      '#title' => $this->t('From'),
      '#title_display' => 'invisible',
      '#min' => min($years),
      '#max' => max($years),
    ];

    $form['copy']['text2'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('to'),
      '#attributes' => [
        'class' => ['form-item'],
      ],
    ];

    $form['copy']['to'] = [
      '#type' => 'number',
      '#title' => $this->t('To'),
      '#title_display' => 'invisible',
      '#min' => min($years),
      '#max' => max($years),
    ];

    $form['copy']['submitCopy'] = [
      '#type' => 'submit',
      '#value' => $this->t('Copy'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#parents'];

    // Save the values for a given year.
    if (isset($parents[1]) && $parents[0] === 'values' && is_numeric($parents[1])) {
      $year = $form_state->getTriggeringElement()['#parents'][1];
      foreach ($form_state->getValue(['values', $year, 'data'], []) as $id => $value) {
        if ($id) {
          if (!empty($value['id']) && !empty($value['name'])) {
            $this->connection->update('mf_hoofdstuk')
              ->fields([
                'naam' => $value['name'],
                'hoofdstuk_minfin_id' => $value['id'],
              ])
              ->condition('hoofdstuk_id', $id, '=')
              ->execute();
          }
          else {
            $this->connection->delete('mf_hoofdstuk')
              ->condition('hoofdstuk_id', $id, '=')
              ->execute();
          }
        }
        else {
          if (!empty($value['id']) && !empty($value['name'])) {
            $this->connection->insert('mf_hoofdstuk')
              ->fields([
                'naam' => $value['name'],
                'hoofdstuk_minfin_id' => $value['id'],
                'jaar' => $year,
              ])
              ->execute();
          }
        }
      }
    }

    // Copy the values from one year to another.
    elseif (isset($parents[0]) && $parents[0] === 'copy') {
      $from = $form_state->getValue(['copy', 'from']);
      $to = $form_state->getValue(['copy', 'to']);
      $result = $this->connection->select('mf_hoofdstuk', 'h')
        ->fields('h', ['naam', 'hoofdstuk_minfin_id'])
        ->condition('jaar', $from, '=')
        ->execute();
      while ($record = $result->fetchAssoc()) {
        $naam = $this->connection->select('mf_hoofdstuk', 'h')
          ->fields('h', ['naam'])
          ->condition('jaar', $to, '=')
          ->condition('hoofdstuk_minfin_id', $record['hoofdstuk_minfin_id'], '=')
          ->execute()->fetchField();
        if (!$naam) {
          $this->connection->insert('mf_hoofdstuk')
            ->fields([
              'naam' => $record['naam'],
              'hoofdstuk_minfin_id' => $record['hoofdstuk_minfin_id'],
              'jaar' => $to,
            ])
            ->execute();
        }
      }
    }
  }

}
