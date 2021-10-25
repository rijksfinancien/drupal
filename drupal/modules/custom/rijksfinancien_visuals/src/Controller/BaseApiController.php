<?php

namespace Drupal\rijksfinancien_visuals\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base class with functions for the API calls.
 */
abstract class BaseApiController extends ControllerBase {

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The raw $_GET parameters.
   *
   * @var array
   */
  protected $getParams;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * BaseApiController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(Connection $database, CacheBackendInterface $cacheBackend, RequestStack $requestStack) {
    $this->database = $database;
    $this->cacheBackend = $cacheBackend;
    $this->getParams = $requestStack->getCurrentRequest()->query->all();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('cache.default'),
      $container->get('request_stack')
    );
  }

  /**
   * Output the data as a json response.
   *
   * @param mixed $data
   *   The data.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  protected function jsonResponse($data): JsonResponse {
    $response = new JsonResponse($data);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    return $response;
  }

  /**
   * Check if we have a cached version.
   *
   * @param string $cid
   *   The cache id.
   *
   * @return mixed|null
   *   The cached version or NULL.
   */
  protected function getCachedVersion(string $cid) {
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      return $cache->data;
    }
    return NULL;
  }

  /**
   * Save the data in the cache.
   *
   * @param string $cid
   *   The cache id.
   * @param mixed $data
   *   The data.
   */
  protected function setCachedVersion(string $cid, $data): void {
    $this->cacheBackend->set($cid, $data);
  }

}
