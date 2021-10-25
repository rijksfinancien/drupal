<?php

namespace Drupal\minfin_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Creates the year pages.
 */
class YearController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Node view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * ChapterRoutes constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
    $this->nodeStorage = $this->entityTypeManager()->getStorage('node');
    $this->nodeViewBuilder = $this->entityTypeManager()->getViewBuilder('node');
  }

  /**
   * Build the actual page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content(): array {
    $path = $this->config('system.site')->get('page.front');
    if (strpos($path, '/node/') === 0 && is_numeric(substr($path, 6))) {
      $nid = substr($path, 6);
      $node = $this->nodeStorage->load($nid);
      $build = $this->nodeViewBuilder->view($node);
    }

    if (empty($build)) {
      throw new NotFoundHttpException();
    }

    return $build;
  }

}
