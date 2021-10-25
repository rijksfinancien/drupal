<?php

namespace Drupal\minfin_kamerstuk\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\minfin\SolrKamerstukClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Re-index the kamerstukken.
 */
class KamerstukReindexForm extends FormBase {

  /**
   * The connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
   * @param \Drupal\minfin\SolrKamerstukClientInterface $solrKamerstukClient
   *   The SOLR kamerstuk client.
   */
  public function __construct(Connection $connection, SolrKamerstukClientInterface $solrKamerstukClient) {
    $this->connection = $connection;
    $this->solrKamerstukClient = $solrKamerstukClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('minfin.solr_kamerstuk'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_kamerstuk_reindex_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Re-index kamerstukken'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Remove all kamerstukken from the index.
    $this->solrKamerstukClient->deleteAll();

    // Add the kamerstukken to the batch.
    $operations = [];
    $result = $this->connection->select('mf_kamerstuk', 'k')
      ->fields('k', ['kamerstuk_id'])
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $operations[] = ['minfin_kamerstuk_reindex_batch_run', [FALSE, (int) $record['kamerstuk_id']]];
    }

    // Add the kamerstuk bijlages to the batch.
    $result = $this->connection->select('mf_kamerstuk_bijlage', 'kb')
      ->fields('kb', ['kamerstuk_bijlage_id'])
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $operations[] = ['minfin_kamerstuk_reindex_batch_run', [TRUE, (int) $record['kamerstuk_bijlage_id']]];
    }

    $batch = [
      'title' => $this->t('Re-index kamerstukken'),
      'operations' => $operations,
      'finished' => 'minfin_kamerstuk_reindex_batch_finished',
      'init_message' => $this->t('Starting with re-index.'),
      'progress_message' => $this->t('Processed @current out of @total items.'),
      'error_message' => $this->t('The re-index process has encountered an error.'),
      'file' => drupal_get_path('module', 'minfin_kamerstuk') . '/batch/kamerstuk_reindex.inc',
    ];

    batch_set($batch);
  }

}
