<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\minfin\MinfinNamingServiceInterface;
use Drupal\minfin_solr\SolrClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * The base API.
 *
 * @SWG\Swagger(
 *   basePath="/",
 * @SWG\Info(
 *     version="1.0.0",
 *     title="Rijksfinancien: Internal API",
 *     description="This page contains a description of the API calls used to generate the visualisation. The output of these API's is pretty limited. For the more public API go to <a href='/open-data/api-documentatie'>Open data API</a>.",
 *   )
 * )
 */
class BaseApiController extends ControllerBase {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The transliteration.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * The minfin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingServiceInterface
   */
  protected $minfinNamingService;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The SOLR client.
   *
   * @var \Drupal\minfin_solr\SolrClientInterface
   */
  protected $solrClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.default'),
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('transliteration'),
      $container->get('minfin.naming'),
      $container->get('config.factory'),
      $container->get('minfin_solr.solr_client'),
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNamingService
   *   The minfin naming service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\minfin_solr\SolrClientInterface $solrClient
   *   The SOLR client.
   */
  public function __construct(CacheBackendInterface $cacheBackend, Connection $connection, RequestStack $requestStack, TransliterationInterface $transliteration, MinfinNamingServiceInterface $minfinNamingService, ConfigFactoryInterface $configFactory, SolrClientInterface $solrClient) {
    $this->cacheBackend = $cacheBackend;
    $this->connection = $connection;
    $this->request = $requestStack->getCurrentRequest();
    $this->transliteration = $transliteration;
    $this->minfinNamingService = $minfinNamingService;
    $this->configFactory = $configFactory;
    $this->solrClient = $solrClient;
  }

  /**
   * Output the data as a json response.
   *
   * @param mixed $data
   *   The data.
   * @param int|null $status
   *   The status.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json.
   */
  protected function jsonResponse($data, ?int $status = NULL): JsonResponse {
    if (!$status) {
      $status = !empty($data) ? 200 : 404;
    }

    $response = new JsonResponse($data, $status);
    $response->headers->set('Access-Control-Allow-Origin', '*');
    return $response;
  }

  /**
   * Output the data as a cacheable json response.
   *
   * @param mixed $data
   *   The data.
   * @param array $cacheMetadata
   *   Render array with the cache meta data.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Json.
   */
  protected function cacheableJsonResponse($data, array $cacheMetadata): CacheableJsonResponse {
    $response = new CacheableJsonResponse($data);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cacheMetadata]));
    return $response;
  }

  /**
   * Output the data as a csv response.
   *
   * @param string $filename
   *   The filename.
   * @param array $data
   *   The data to be outputed as csv.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   CSV.
   */
  protected function csvResponse($filename, array $data) {
    $fh = fopen('php://temp', 'rwb');
    fputcsv($fh, array_keys(current($data)));
    foreach ($data as $row) {
      fputcsv($fh, $row);
    }
    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);

    $response = new Response($csv);
    $disposition = $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      $this->getSafeFilename($filename) . '.csv'
    );

    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    $response->headers->set('Content-Disposition', $disposition);

    return $response;
  }

  /**
   * Makes a filename safe for download.
   *
   * @param string $filename
   *   The filename.
   *
   * @return string
   *   The filename.
   */
  private function getSafeFilename($filename): string {
    $filename = $this->transliteration->transliterate($filename, 'en', '_');
    $filename = strtolower($filename);
    $filename = preg_replace('/[^a-z0-9_]+/', '_', $filename);
    return preg_replace('/_+/', '_', $filename);
  }

}
