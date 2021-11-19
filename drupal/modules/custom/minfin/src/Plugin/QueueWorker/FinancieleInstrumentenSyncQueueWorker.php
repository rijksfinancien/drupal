<?php

namespace Drupal\minfin\Plugin\QueueWorker;

use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\minfin\SolrWieOntvingenClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a queue to sync the Financiele instrumenten with SOLR.
 *
 * @QueueWorker(
 *   id = "financiele_instrumenten_sync_queue",
 *   title = @Translation("Financiele instrumenten SOLR sync"),
 *   cron = {"time" = 50}
 * )
 */
class FinancieleInstrumentenSyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The query length.
   */
  const QUERYLENGTH = 500;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The SOLR Client.
   *
   * @var \Drupal\minfin\SolrWieOntvingenClientInterface
   */
  protected $solrWieOntvingenClient;

  /**
   * The queue.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

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
   * @param \Drupal\minfin\SolrWieOntvingenClientInterface $solrWieOntvingenClient
   *   The wie ontvingen SOLR client.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue factory.
   */
  public function __construct(array $configuration, string $pluginId, $pluginDefinition, Connection $connection, SolrWieOntvingenClientInterface $solrWieOntvingenClient, QueueFactory $queueFactory) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->connection = $connection;
    $this->solrWieOntvingenClient = $solrWieOntvingenClient;
    $this->queue = $queueFactory->get('financiele_instrumenten_sync_queue', TRUE);
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
      $container->get('minfin.solr_wie_ontvingen'),
      $container->get('queue'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    $function = $item['function'];
    unset($item['function']);
    $item['year'] = (int) $item['year'];
    call_user_func_array([$this, $function], $item);
  }

  /**
   * Sync the item with SOLR.
   *
   * @param array $data
   *   The data to sync.
   */
  public function syncItem(array $data) {
    $this->solrWieOntvingenClient->update($data);
  }

  /**
   * Sync the item with SOLR with a ?commit=true.
   *
   * @param array $data
   *   The data to sync.
   */
  public function syncItemWithCommit(array $data) {
    $this->solrWieOntvingenClient->update($data, TRUE);
  }

  /**
   * Add the queue items to start the solr synchronization.
   *
   * @param int $year
   *   The year.
   */
  protected function syncSolr(int $year): void {
    $data = ['year' => $year, 'start' => 0];

    $this->solrWieOntvingenClient->deleteAll($year);
    $this->queue->createItem(['function' => 'getHoofdstukkenPerOntvanger'] + $data);
    $this->queue->createItem(['function' => 'getHoofdstukkenPerRegeling'] + $data);
    $this->queue->createItem(['function' => 'getArtikelen'] + $data);
    $this->queue->createItem(['function' => 'getArtikelenPerOntvanger'] + $data);
    $this->queue->createItem(['function' => 'getArtikelenPerRegeling'] + $data);
    $this->queue->createItem(['function' => 'getRegelingen'] + $data);
    $this->queue->createItem(['function' => 'getRegelingenPerHoofdstuk'] + $data);
    $this->queue->createItem(['function' => 'getRegelingenPerArtikel'] + $data);
    $this->queue->createItem(['function' => 'getRegelingenPerOntvanger'] + $data);
    $this->queue->createItem(['function' => 'getOntvangers'] + $data);
    $this->queue->createItem(['function' => 'getOntvangersPerHoofdstuk'] + $data);
    $this->queue->createItem(['function' => 'getOntvangersPerArtikel'] + $data);
    $this->queue->createItem(['function' => 'getOntvangersPerRegeling'] + $data);

    // We leave the actual chapters for last, since this is the shortest list.
    // we'll use it to directly update the SOLR index.
    $this->queue->createItem(['function' => 'getHoofdstukken'] + $data);
  }

  /**
   * Get Hoofdstukken.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getHoofdstukken(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('h.naam');
    $query->groupBy('h.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getHoofdstukken', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItemWithCommit',
        'data' => [
          'id' => '',
          'name' => $record['naam'],
          'year' => $year,
          'type' => 'hoofdstuk',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['naam'],
          'grouped_by_type' => 'hoofdstuk',
        ],
      ]);
    }
  }

  /**
   * Get Hoofdstukken per ontvanger.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getHoofdstukkenPerOntvanger(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['ontvanger']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('h.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getHoofdstukkenPerOntvanger', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['naam'],
          'year' => $year,
          'type' => 'hoofdstuk',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['ontvanger'],
          'grouped_by_type' => 'ontvanger',
        ],
      ]);
    }
  }

  /**
   * Get Hoofdstukken per regeling.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getHoofdstukkenPerRegeling(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['regeling']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('h.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getHoofdstukkenPerRegeling', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['naam'],
          'year' => $year,
          'type' => 'hoofdstuk',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['regeling'],
          'grouped_by_type' => 'regeling',
        ],
      ]);
    }
  }

  /**
   * Get Artikelen.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getArtikelen(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->join('mf_artikel', 'a', 'a.artikel_minfin_id = fi.artikel_minfin_id AND a.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND a.jaar = fi.jaar');
    $query->addField('a', 'naam', 'artikel_naam');
    $query->addField('h', 'naam', 'hoofdstuk_naam');
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('h.naam');
    $query->orderBy('a.naam');
    $query->groupBy('h.naam');
    $query->groupBy('a.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getArtikelen', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
          'year' => $year,
          'type' => 'artikel',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
          'grouped_by_type' => 'artikel',
        ],
      ]);
    }
  }

  /**
   * Get Artikelen per ontvanger.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getArtikelenPerOntvanger(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->join('mf_artikel', 'a', 'a.artikel_minfin_id = fi.artikel_minfin_id AND a.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND a.jaar = fi.jaar');
    $query->fields('fi', ['ontvanger']);
    $query->addField('a', 'naam', 'artikel_naam');
    $query->addField('h', 'naam', 'hoofdstuk_naam');
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('h.naam');
    $query->groupBy('a.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getArtikelenPerOntvanger', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
          'year' => $year,
          'type' => 'artikel',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['ontvanger'],
          'grouped_by_type' => 'ontvanger',
        ],
      ]);
    }
  }

  /**
   * Get Artikelen per regeling.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getArtikelenPerRegeling(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->join('mf_artikel', 'a', 'a.artikel_minfin_id = fi.artikel_minfin_id AND a.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND a.jaar = fi.jaar');
    $query->fields('fi', ['regeling']);
    $query->addField('a', 'naam', 'artikel_naam');
    $query->addField('h', 'naam', 'hoofdstuk_naam');
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('h.naam');
    $query->groupBy('a.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getArtikelenPerRegeling', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
          'year' => $year,
          'type' => 'artikel',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['regeling'],
          'grouped_by_type' => 'regeling',
        ],
      ]);
    }
  }

  /**
   * Get Regelingen.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getRegelingen(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['regeling']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getRegelingen', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['regeling'],
          'year' => $year,
          'type' => 'regeling',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['regeling'],
          'grouped_by_type' => 'regeling',
        ],
      ]);
    }
  }

  /**
   * Get Regelingen per hoofdstuk.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getRegelingenPerHoofdstuk(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['regeling']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('h.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getRegelingenPerHoofdstuk', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['regeling'],
          'year' => $year,
          'type' => 'regeling',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['naam'],
          'grouped_by_type' => 'hoofdstuk',
        ],
      ]);
    }
  }

  /**
   * Get Regelingen per artikel.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getRegelingenPerArtikel(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->join('mf_artikel', 'a', 'a.artikel_minfin_id = fi.artikel_minfin_id AND a.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND a.jaar = fi.jaar');
    $query->fields('fi', ['regeling']);
    $query->addField('a', 'naam', 'artikel_naam');
    $query->addField('h', 'naam', 'hoofdstuk_naam');
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('h.naam');
    $query->groupBy('a.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getRegelingenPerArtikel', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['regeling'],
          'year' => $year,
          'type' => 'regeling',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
          'grouped_by_type' => 'artikel',
        ],
      ]);
    }
  }

  /**
   * Get Regelingen per ontvanger.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getRegelingenPerOntvanger(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['regeling', 'ontvanger']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('fi.ontvanger');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getRegelingenPerOntvanger', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['regeling'],
          'year' => $year,
          'type' => 'regeling',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['ontvanger'],
          'grouped_by_type' => 'ontvanger',
        ],
      ]);
    }
  }

  /**
   * Get Ontvangers.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getOntvangers(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['ontvanger']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getOntvangers', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['ontvanger'],
          'year' => $year,
          'type' => 'ontvanger',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['ontvanger'],
          'grouped_by_type' => 'ontvanger',
        ],
      ]);
    }
  }

  /**
   * Get Ontvangers per hoofdstuk.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getOntvangersPerHoofdstuk(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['ontvanger']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('h.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getOntvangersPerHoofdstuk', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['ontvanger'],
          'year' => $year,
          'type' => 'ontvanger',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['naam'],
          'grouped_by_type' => 'hoofdstuk',
        ],
      ]);
    }
  }

  /**
   * Get Ontvangers per artikel.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getOntvangersPerArtikel(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->join('mf_artikel', 'a', 'a.artikel_minfin_id = fi.artikel_minfin_id AND a.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND a.jaar = fi.jaar');
    $query->fields('fi', ['ontvanger']);
    $query->addField('a', 'naam', 'artikel_naam');
    $query->addField('h', 'naam', 'hoofdstuk_naam');
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('h.naam');
    $query->groupBy('a.naam');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getOntvangersPerArtikel', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['ontvanger'],
          'year' => $year,
          'type' => 'ontvanger',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
          'grouped_by_type' => 'artikel',
        ],
      ]);
    }
  }

  /**
   * Get Ontvangers per regeling.
   *
   * @param int $year
   *   The year.
   * @param int $start
   *   The query offset.
   */
  protected function getOntvangersPerRegeling(int $year, int $start): void {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['ontvanger', 'regeling']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('fi.regeling');
    $query->range(($start * self::QUERYLENGTH), self::QUERYLENGTH);
    $count = $query->countQuery()->execute()->fetchField();
    if ($count >= self::QUERYLENGTH) {
      $this->queue->createItem(['function' => 'getOntvangersPerRegeling', 'year' => $year, 'start' => $start + 1]);
    }

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $this->queue->createItem([
        'function' => 'syncItem',
        'data' => [
          'id' => '',
          'name' => $record['ontvanger'],
          'year' => $year,
          'type' => 'ontvanger',
          'amount' => (float) $record['bedrag'],
          'grouped_by' => $record['regeling'],
          'grouped_by_type' => 'regeling',
        ],
      ]);
    }
  }

}
