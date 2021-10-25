<?php

namespace Drupal\minfin;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;

/**
 * Service for common minfin related functions.
 */
class MinfinSourceFileService implements MinfinSourceFileServiceInterface {

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entityTypeManager) {
    $this->connection = $connection;
    $this->fileStorage = $entityTypeManager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public function getLastSourceFile(string $importType, ?string $subType = NULL, ?int $year = NULL): ?File {
    $query = $this->connection->select('mf_log', 'l');
    $query->fields('l', ['fid']);
    $query->condition('state', 1, '=');
    $query->condition('type', $importType, '=');
    if ($subType) {
      $query->condition('sub_type', $subType, '=');
    }
    if ($year) {
      $query->condition('year', $year, '=');
    }
    $query->orderBy('created', 'DESC');
    $query->range(0, 1);
    $fid = $query->execute()->fetchField();
    return $this->fileStorage->load($fid);
  }

}
