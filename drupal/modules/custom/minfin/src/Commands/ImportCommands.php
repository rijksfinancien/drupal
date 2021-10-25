<?php

namespace Drupal\minfin\Commands;

use Drupal\Core\Database\Connection;
use Drush\Commands\DrushCommands;

/**
 * A Drush import command.
 */
class ImportCommands extends DrushCommands {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Import constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    parent::__construct();
    $this->connection = $connection;
  }

  /**
   * Clean database function.
   *
   * @command minfin:cleanup
   * @aliases minfin-cleanup
   * @usage minfin:cleanup
   *   Cleanup the database.
   */
  public function cleanup(): void {
    $this->connection->delete('mf_artikel')->execute();
    $this->connection->delete('mf_artikelonderdeel')->execute();
    $this->connection->delete('mf_b_tabel')->execute();
    $this->connection->delete('mf_hoofdstuk')->execute();
    $this->connection->delete('mf_hoofdstuk_heeft_minister')->execute();
    $this->connection->delete('mf_instrument_of_uitsplitsing_apparaat')->execute();
    $this->connection->delete('mf_minister')->execute();
    $this->connection->delete('mf_regeling_detailniveau')->execute();
    $this->connection->delete('mf_voorgaand_artikel')->execute();
    $this->connection->delete('mf_voorgaand_hoofdstuk')->execute();
  }

}
