<?php

namespace Drupal\minfin_beleidsevaluaties\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handle the title autocomplete functionality.
 */
class TitleAutoCompleteController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A Json object.
   */
  public function handleAutocomplete(Request $request): JsonResponse {
    $input = $request->query->get('q');

    $query = $this->connection->select('mf_beleidsevaluatie', 'b');
    $query->distinct();
    $query->fields('b', ['titel']);
    $query->condition('b.titel', '%' . $input . '%', 'LIKE');
    $query->orderBy('titel');
    $query->range(0, 10);
    $results = $query->execute()->fetchAllKeyed(0, 0);
    return new JsonResponse(array_values($results));
  }

}
