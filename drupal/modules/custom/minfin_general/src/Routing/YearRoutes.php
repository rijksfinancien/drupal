<?php

namespace Drupal\minfin_general\Routing;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Handles the year routes.
 */
class YearRoutes implements ContainerInjectionInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The minfin service.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('minfin.minfin')
    );
  }

  /**
   * ChapterRoutes constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   The minfin service.
   */
  public function __construct(Connection $connection, MinfinServiceInterface $minfinService) {
    $this->connection = $connection;
    $this->minfinService = $minfinService;
  }

  /**
   * Generate the routes.
   *
   * @return array
   *   An array with routes.
   */
  public function routes() {
    $routes = [];
    foreach ($this->minfinService->getAvailableYears() as $jaar) {
      $routes['minfin_general.year.' . $jaar] = new Route(
        '/' . $jaar,
        [
          '_controller' => '\Drupal\minfin_general\Controller\YearController::content',
          '_title' => (string) $jaar,
          'year' => $jaar,
        ],
        [
          '_permission' => 'access content',
        ]
      );
    }

    return $routes;
  }

}
