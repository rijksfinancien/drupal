<?php

namespace Drupal\minfin\Plugin\QueueWorker;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a queue behind the budgettaire tabellen history importer.
 *
 * @QueueWorker(
 *   id = "budgettaire_tabellen_history_queue",
 *   title = @Translation("Budgettaire tabellen history import"),
 *   cron = {"time" = 50}
 * )
 */
class ImportBudgettaireTabellenHistoryQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The messenger.
   *
   * @var \Drupal\pathauto\MessengerInterface
   */
  protected $messenger;

  /**
   * The cache tag invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a ImportBudgettaireTabellenHistoryQueueWorker object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tag invalidator.
   */
  public function __construct(array $configuration, string $pluginId, $pluginDefinition, Connection $connection, MessengerInterface $messenger, CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->connection = $connection;
    $this->messenger = $messenger;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('database'),
      $container->get('messenger'),
      $container->get('cache_tags.invalidator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    if (isset($item['id'], $item['jaar'], $item['fase'])) {
      $iterator = new \ArrayIterator();
      $imported = [];
      $query = $this->connection->select('mf_voorgaand_regeling_detailniveau', 'vrd');
      $query->addField('vrd', 'voorgaand_regeling_detailniveau_id', 'id');
      $query->addField('vrd', 'voorgaand_jaar', 'jaar');
      $query->addField('vrd', 'voorgaand_fase', 'fase');
      $query->condition('vrd.regeling_detailniveau_id', $item['id'], '=');
      $query->condition('vrd.jaar', $item['jaar'], '=');
      $query->condition('vrd.fase', $item['fase'], '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $iterator->append($record);
      }

      foreach ($iterator as $data) {
        if (!in_array($data, $imported, TRUE)) {
          $query = $this->connection->select('mf_voorgaand_regeling_detailniveau', 'vrd');
          $query->addField('vrd', 'voorgaand_regeling_detailniveau_id', 'id');
          $query->addField('vrd', 'voorgaand_jaar', 'jaar');
          $query->addField('vrd', 'voorgaand_fase', 'fase');
          $query->condition('vrd.regeling_detailniveau_id', $data['id'], '=');
          $query->condition('vrd.jaar', $data['jaar'], '=');
          $query->condition('vrd.fase', $data['fase'], '=');
          $result = $query->execute();
          while ($record = $result->fetchAssoc()) {
            $record['jaar'] = (int) $record['jaar'];
            $record['id'] = (int) $record['id'];
            if ($record['fase'] === $item['fase'] && ($record['jaar'] - 1 === $item['jaar'] || $record['jaar'] + 1 === $item['jaar'])) {
              $this->insertRecord($item['id'], $item['jaar'], $item['fase'], $record['id'], $record['jaar'], $record['fase']);
            }
            elseif ($record['jaar'] - 1 <= $item['jaar'] || $record['jaar'] + 1 >= $item['jaar']) {
              $iterator->append($record);
            }
          }
          $imported[] = $data;
        }
      }
    }
  }

  /**
   * Insert the record into the database.
   *
   * @param int $regelingDetailniveauId
   *   The $regelingDetailniveauId.
   * @param int $jaar
   *   The year.
   * @param string $fase
   *   The phase.
   * @param int $voorgaandRegelingDetailniveauId
   *   The previous $regelingDetailniveauId.
   * @param int $voorgaandJaar
   *   The previous year.
   * @param string $voorgaandFase
   *   The previous phase.
   *
   * @see ImportBudgettaireTabellenHistoryForm::insertRecord();
   */
  private function insertRecord(int $regelingDetailniveauId, int $jaar, string $fase, int $voorgaandRegelingDetailniveauId, int $voorgaandJaar, string $voorgaandFase): void {
    try {
      $this->connection->merge('mf_voorgaand_regeling_detailniveau')
        ->keys([
          'voorgaand_regeling_detailniveau_id' => $regelingDetailniveauId,
          'regeling_detailniveau_id' => $voorgaandRegelingDetailniveauId,
        ])
        ->fields([
          'voorgaand_jaar' => $jaar,
          'voorgaand_fase' => $fase,
          'jaar' => $voorgaandJaar,
          'fase' => $voorgaandFase,
          'type' => 'reverse',
        ])
        ->execute();

      $this->connection->merge('mf_voorgaand_regeling_detailniveau')
        ->keys([
          'voorgaand_regeling_detailniveau_id' => $voorgaandRegelingDetailniveauId,
          'regeling_detailniveau_id' => $regelingDetailniveauId,
        ])
        ->fields([
          'voorgaand_jaar' => $voorgaandJaar,
          'voorgaand_fase' => $voorgaandFase,
          'jaar' => $jaar,
          'fase' => $fase,
          'type' => 'normal',
        ])
        ->execute();

      // @todo check if we can invalidate this cache per cron instead of per item.
      $this->cacheTagsInvalidator->invalidateTags(['minfin_import:budgettaire_tabellen_history']);
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
    }
  }

}
