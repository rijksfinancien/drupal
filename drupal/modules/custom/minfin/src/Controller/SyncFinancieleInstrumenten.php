<?php

namespace Drupal\minfin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\minfin\SolrWieOntvingenClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tmp controller to sync financiele instrumenten.
 */
class SyncFinancieleInstrumenten extends ControllerBase {

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
   * Constructs an ImportUitzonderingenController object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\minfin\SolrWieOntvingenClientInterface $solrWieOntvingenClient
   *   The wie ontvingen SOLR client.
   */
  public function __construct(Connection $connection, SolrWieOntvingenClientInterface $solrWieOntvingenClient) {
    $this->connection = $connection;
    $this->solrWieOntvingenClient = $solrWieOntvingenClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('minfin.solr_wie_ontvingen'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildPage(): array {
    $this->solrWieOntvingenClient->deleteAll();

    $year = 2020;
    $values = [];
    array_push($values, ...$this->getHoofdstukken($year));
    array_push($values, ...$this->getHoofdstukkenPerOntvanger($year));
    array_push($values, ...$this->getHoofdstukkenPerRegeling($year));
    array_push($values, ...$this->getArtikelen($year));
    array_push($values, ...$this->getArtikelenPerOntvanger($year));
    array_push($values, ...$this->getArtikelenPerRegeling($year));
    array_push($values, ...$this->getRegelingen($year));
    array_push($values, ...$this->getRegelingenPerHoofdstuk($year));
    array_push($values, ...$this->getRegelingenPerArtikel($year));
    array_push($values, ...$this->getRegelingenPerOntvanger($year));
    array_push($values, ...$this->getOntvangers($year));
    array_push($values, ...$this->getOntvangersPerHoofdstuk($year));
    array_push($values, ...$this->getOntvangersPerArtikel($year));
    array_push($values, ...$this->getOntvangersPerRegeling($year));
    foreach ($values as $data) {
      $this->solrWieOntvingenClient->update($data);
    }

    return ['#markup' => 'done'];
  }

  /**
   * Get Hoofdstukken.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The hoofdstukken.
   */
  private function getHoofdstukken(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('h.naam');
    $query->groupBy('h.naam');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['naam'],
        'year' => $year,
        'type' => 'hoofdstuk',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['naam'],
        'grouped_by_type' => 'hoofdstuk',
      ];
    }
    return $data;
  }

  /**
   * Get Hoofdstukken.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The hoofdstukken.
   */
  private function getHoofdstukkenPerOntvanger(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['ontvanger']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('h.naam');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['naam'],
        'year' => $year,
        'type' => 'hoofdstuk',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['ontvanger'],
        'grouped_by_type' => 'ontvanger',
      ];
    }
    return $data;
  }

  /**
   * Get Hoofdstukken.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The hoofdstukken.
   */
  private function getHoofdstukkenPerRegeling(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['regeling']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('h.naam');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['naam'],
        'year' => $year,
        'type' => 'hoofdstuk',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['regeling'],
        'grouped_by_type' => 'regeling',
      ];
    }
    return $data;
  }

  /**
   * Get Artikelen.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The artikelen.
   */
  private function getArtikelen(int $year) {
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
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
        'year' => $year,
        'type' => 'artikel',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
        'grouped_by_type' => 'artikel',
      ];
    }
    return $data;
  }

  /**
   * Get Artikelen.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The artikelen.
   */
  private function getArtikelenPerOntvanger(int $year) {
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
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
        'year' => $year,
        'type' => 'artikel',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['ontvanger'],
        'grouped_by_type' => 'ontvanger',
      ];
    }
    return $data;
  }

  /**
   * Get Artikelen.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The artikelen.
   */
  private function getArtikelenPerRegeling(int $year) {
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
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
        'year' => $year,
        'type' => 'artikel',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['regeling'],
        'grouped_by_type' => 'regeling',
      ];
    }
    return $data;
  }

  /**
   * Get Regelingen.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The regelingen.
   */
  private function getRegelingen(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['regeling']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['regeling'],
        'year' => $year,
        'type' => 'regeling',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['regeling'],
        'grouped_by_type' => 'regeling',
      ];
    }
    return $data;
  }

  /**
   * Get Regelingen.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The regelingen.
   */
  private function getRegelingenPerHoofdstuk(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['regeling']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('h.naam');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['regeling'],
        'year' => $year,
        'type' => 'regeling',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['naam'],
        'grouped_by_type' => 'hoofdstuk',
      ];
    }
    return $data;
  }

  /**
   * Get Regelingen.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The regelingen.
   */
  private function getRegelingenPerArtikel(int $year) {
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
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['regeling'],
        'year' => $year,
        'type' => 'regeling',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
        'grouped_by_type' => 'artikel',
      ];
    }
    return $data;
  }

  /**
   * Get Regelingen.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The regelingen.
   */
  private function getRegelingenPerOntvanger(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['regeling', 'ontvanger']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.regeling');
    $query->groupBy('fi.regeling');
    $query->groupBy('fi.ontvanger');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['regeling'],
        'year' => $year,
        'type' => 'regeling',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['ontvanger'],
        'grouped_by_type' => 'ontvanger',
      ];
    }
    return $data;
  }

  /**
   * Get Ontvangers.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The ontvangers.
   */
  private function getOntvangers(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['ontvanger']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['ontvanger'],
        'year' => $year,
        'type' => 'ontvanger',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['ontvanger'],
        'grouped_by_type' => 'ontvanger',
      ];
    }
    return $data;
  }

  /**
   * Get Ontvangers.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The ontvangers.
   */
  private function getOntvangersPerHoofdstuk(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
    $query->fields('fi', ['ontvanger']);
    $query->fields('h', ['naam']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('h.naam');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['ontvanger'],
        'year' => $year,
        'type' => 'ontvanger',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['naam'],
        'grouped_by_type' => 'hoofdstuk',
      ];
    }
    return $data;
  }

  /**
   * Get Ontvangers.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The ontvangers.
   */
  private function getOntvangersPerArtikel(int $year) {
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
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['ontvanger'],
        'year' => $year,
        'type' => 'ontvanger',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['hoofdstuk_naam'] . ': ' . $record['artikel_naam'],
        'grouped_by_type' => 'artikel',
      ];
    }
    return $data;
  }

  /**
   * Get Ontvangers.
   *
   * @param int $year
   *   The year.
   *
   * @return array
   *   The ontvangers.
   */
  private function getOntvangersPerRegeling(int $year) {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->fields('fi', ['ontvanger', 'regeling']);
    $query->addExpression('SUM(fi.bedrag)', 'bedrag');
    $query->condition('fi.jaar', $year, '=');
    $query->orderBy('fi.ontvanger');
    $query->groupBy('fi.ontvanger');
    $query->groupBy('fi.regeling');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[] = [
        'id' => '',
        'name' => $record['ontvanger'],
        'year' => $year,
        'type' => 'ontvanger',
        'amount' => (float) $record['bedrag'],
        'grouped_by' => $record['regeling'],
        'grouped_by_type' => 'regeling',
      ];
    }
    return $data;
  }

}
