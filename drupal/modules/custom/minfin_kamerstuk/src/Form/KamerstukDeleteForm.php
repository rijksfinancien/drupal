<?php

namespace Drupal\minfin_kamerstuk\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\minfin\KamerstukFormTrait;
use Drupal\minfin\SolrKamerstukClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Delete kamerstukken.
 */
class KamerstukDeleteForm extends FormBase {
  use KamerstukFormTrait;

  /**
   * The connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The file usage.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The SOLR kamerstuk client.
   *
   * @var \Drupal\minfin\SolrKamerstukClientInterface
   */
  protected $solrKamerstukClient;

  /**
   * Constructs an KamerstukReindexForm object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\file\FileUsage\FileUsageInterface $fileUsage
   *   The file usage.
   * @param \Drupal\minfin\SolrKamerstukClientInterface $solrKamerstukClient
   *   The SOLR kamerstuk client.
   */
  public function __construct(Connection $connection, MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager, FileUsageInterface $fileUsage, SolrKamerstukClientInterface $solrKamerstukClient) {
    $this->connection = $connection;
    $this->messenger = $messenger;
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->fileUsage = $fileUsage;
    $this->solrKamerstukClient = $solrKamerstukClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('messenger'),
      $container->get('entity_type.manager'),
      $container->get('file.usage'),
      $container->get('minfin.solr_kamerstuk'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_kamerstuk_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $years = $this->connection->select('mf_kamerstuk', 'k')
      ->distinct(TRUE)
      ->fields('k', ['jaar'])
      ->execute()->fetchAllKeyed(0, 0);

    $form['warning'] = [
      '#markup' => $this->t('Remember this action cannot be undone, any deleted entries need to be re-uploaded manually.'),
    ];

    $form['year'] = [
      '#type' => 'select',
      '#title' => $this->t('Year'),
      '#options' => $years,
      '#empty_option' => $this->t('Select year'),
      '#required' => TRUE,
    ];

    $phases = [];
    foreach (array_keys($this->getAvailableTypes()) as $phase) {
      $phases[$phase] = $phase;
    }
    ksort($phases);
    $form['phase'] = [
      '#type' => 'select',
      '#title' => $this->t('Phase'),
      '#required' => FALSE,
      '#options' => $phases,
      '#empty_option' => $this->t('- Select phase -'),
    ];

    $hoofdstukken = $this->connection->select('mf_kamerstuk', 'k')
      ->distinct(TRUE)
      ->fields('k', ['hoofdstuk_minfin_id'])
      ->execute()->fetchAllKeyed(0, 0);
    $form['hoofdstukMinfinId'] = [
      '#type' => 'select',
      '#title' => $this->t('Chapter'),
      '#required' => FALSE,
      '#options' => $hoofdstukken,
      '#empty_option' => $this->t('- Select chapter -'),
    ];

    $form['phase_suffix'] = [
      '#type' => 'number',
      '#title' => $this->t('Phase suffix'),
      '#states' => [
        'visible' => [
          'select[name="phase"]' => [
            ['value' => 'isb (mvt)'],
            ['value' => 'isb (wet)'],
          ],
        ],
        'required' => [
          'select[name="phase"]' => [
            ['value' => 'isb (mvt)'],
            ['value' => 'isb (wet)'],
          ],
        ],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete kamerstukken'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $hoofdstukMinfinId = $form_state->getValue('hoofdstukMinfinId');

    if ($importPhase = $form_state->getValue('phase', '')) {
      if (in_array($importPhase, ['isb (mvt)', 'isb (wet)'])) {
        $importPhase = $form_state->getValue('phase_suffix') . 'e ' . $importPhase;
      }

      if ($appendix = $this->isKamerstukAppendix($importPhase)) {
        $query = $this->connection->select('mf_kamerstuk_bijlage', 'k');
        $query->fields('k', [
          'kamerstuk_bijlage_id',
          'type',
          'jaar',
          'fase',
          'anchor',
          'hoofdstuk_minfin_id',
        ]);
        $query->condition('k.jaar', $form_state->getValue('year'), '=');
        if ($hoofdstukMinfinId) {
          $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
        }
        $query->condition('k.fase', $this->getRealPhase($importPhase), '=');
        $query->condition('k.type', $this->getRealType($importPhase), '=');
        $kamerstukkenResult = $query->execute();
        while ($record = $kamerstukkenResult->fetchAssoc()) {
          $this->deleteKamerstuk(TRUE, (int) $record['kamerstuk_id'], $record['type'], $record['fase'], (int) $record['jaar'], $record['anchor'], $record['hoofdstuk_minfin_id'] ?? NULL);
        }
      }
      else {
        $query = $this->connection->select('mf_kamerstuk', 'k');
        $query->fields('k', [
          'kamerstuk_id',
          'type',
          'jaar',
          'fase',
          'anchor',
          'hoofdstuk_minfin_id',
        ]);
        $query->condition('k.jaar', $form_state->getValue('year'), '=');
        if ($hoofdstukMinfinId) {
          $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
        }
        $query->condition('k.fase', $this->getRealPhase($importPhase), '=');
        $query->condition('k.type', $this->getRealType($importPhase), '=');
        $kamerstukkenResult = $query->execute();
        while ($record = $kamerstukkenResult->fetchAssoc()) {
          $this->deleteKamerstuk(FALSE, (int) $record['kamerstuk_id'], $record['type'], $record['fase'], (int) $record['jaar'], $record['anchor'], $record['hoofdstuk_minfin_id'] ?? NULL);
        }
      }
    }
    else {
      $query = $this->connection->select('mf_kamerstuk', 'k');
      $query->fields('k', [
        'kamerstuk_id',
        'type',
        'jaar',
        'fase',
        'anchor',
        'hoofdstuk_minfin_id',
      ]);
      $query->condition('k.jaar', $form_state->getValue('year'), '=');
      if ($hoofdstukMinfinId) {
        $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      }
      $kamerstukkenResult = $query->execute();
      while ($record = $kamerstukkenResult->fetchAssoc()) {
        $this->deleteKamerstuk(FALSE, (int) $record['kamerstuk_id'], $record['type'], $record['fase'], (int) $record['jaar'], $record['anchor'], $record['hoofdstuk_minfin_id'] ?? NULL);
      }

      $query = $this->connection->select('mf_kamerstuk_bijlage', 'k');
      $query->fields('k', [
        'kamerstuk_bijlage_id',
        'type',
        'jaar',
        'fase',
        'anchor',
        'hoofdstuk_minfin_id',
      ]);
      $query->condition('k.jaar', $form_state->getValue('year'), '=');
      if ($hoofdstukMinfinId) {
        $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      }
      $kamerstukkenResult = $query->execute();
      while ($record = $kamerstukkenResult->fetchAssoc()) {
        $this->deleteKamerstuk(TRUE, (int) $record['kamerstuk_id'], $record['type'], $record['fase'], (int) $record['jaar'], $record['anchor'], $record['hoofdstuk_minfin_id'] ?? NULL);
      }
    }

    // @todo see if we can specifically clear the minfinbudgetblock cache.
    drupal_flush_all_caches();

    $this->messenger->addStatus($this->t('Delete all kamerstukken for the year @year.', ['@year' => $record['jaar']]));
  }

  /**
   * Delete a specific kamerstuk.
   *
   * @param bool $appendix
   *   Is it an appendix or not.
   * @param int $id
   *   The id.
   * @param string $type
   *   The type.
   * @param string $fase
   *   The phase.
   * @param int $jaar
   *   The year.
   * @param string $anchor
   *   The anchor id.
   * @param string|null $hoofdstukMinfinId
   *   The chapter minfin id.
   */
  private function deleteKamerstuk(bool $appendix, int $id, string $type, string $fase, int $jaar, string $anchor, ?string $hoofdstukMinfinId): void {
    $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
    $tabelId = $appendix ? 'kamerstuk_bijlage_id' : 'kamerstuk_id';

    // Delete the SOLR index.
    $this->solrKamerstukClient->delete($type, $fase, $jaar, $anchor, $hoofdstukMinfinId);

    // Delete the actual db record.
    $this->connection->delete($table)
      ->condition($tabelId, $id, '=')
      ->execute();

    // Check if we've got some files that need to be cleaned up.
    $query = $this->connection->select('file_usage');
    $query->fields('file_usage', ['fid']);
    $query->condition('module', 'minfin', '=');
    $query->condition('type', 'kamerstuk', '=');
    $query->condition('id', $id, '=');
    $result = $query->execute();
    /** @var \Drupal\file\FileInterface $file */
    if (($fid = $result->fetchField()) && $file = $this->fileStorage->load($fid)) {
      $this->fileUsage->delete($file, 'minfin', 'kamerstuk', $id);
    }
  }

}
