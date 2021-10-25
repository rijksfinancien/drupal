<?php

namespace Drupal\minfin_solr;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;

/**
 * Defines the SOLR client used for indexing and searching.
 */
class SolrClient implements SolrClientInterface {

  /**
   * The json serializer.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $json;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * THe config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The error list.
   *
   * @var array
   */
  protected $errors = [];

  /**
   * The SOLR core.
   *
   * @var string
   */
  protected $core;

  /**
   * Constructs a SolrClient object.
   *
   * @param \Drupal\Component\Serialization\Json $json
   *   The JSON serializer.
   * @param \GuzzleHttp\Client $httpClient
   *   The HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger channel factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(Json $json, Client $httpClient, LoggerChannelFactoryInterface $loggerChannelFactory, ConfigFactoryInterface $configFactory) {
    $this->json = $json;
    $this->client = $httpClient;
    $this->logger = $loggerChannelFactory->get('minfin_solr.solr_client');
    $this->config = $configFactory->get('minfin_solr.solr.settings');
    $this->core = 'search';
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * {@inheritdoc}
   */
  public function setCore(string $core): void {
    $this->core = $core;
  }

  /**
   * {@inheritdoc}
   */
  public function search(int $page, int $recordsPerPage, ?string $search, ?string $sort, string $type = 'all', array $activeFacets = []): array {
    $query = [
      'q' => $search ? trim($search) : '*:*',
      'rows' => $recordsPerPage,
      'start' => $recordsPerPage * ($page - 1),
      'facet' => 'on',
      'facet.limit' => -1,
      'facet.mincount' => 1,
      'sort' => $sort,
    ];

    if ($activeFacets) {
      $fq = [];
      foreach ($activeFacets as $k => $values) {
        foreach ($values as $v) {
          if (preg_match('/\[.* TO .*]/', $v)) {
            $fq[] = $k . ':' . $v . '';
          }
          else {
            $fq[] = $k . ':"' . $v . '"';
          }
        }
      }
      $query['fq'] = implode(' AND ', $fq);
    }

    $solrHandler = 'select';
    switch ($type) {
      case 'open_data':
        $solrHandler = 'select_open_data';
        break;

      case 'rbv':
        $solrHandler = 'select_rbv';
        break;

      case 'rijksbegroting':
        $solrHandler = 'select_rijksbegroting';
        break;
    }

    $numFound = 0;
    $rows = [];
    $facets = [];
    if ($result = $this->getRequest('search', $solrHandler . '?' . $this->buildSolrQuery($query))) {
      $numFound = $result['response']['numFound'] ?? 0;
      foreach ($result['response']['docs'] ?? [] as $doc) {
        if (!empty($doc['id']) && !empty($result['highlighting'][$doc['id']])) {
          $doc['highlighting_title'] = $result['highlighting'][$doc['id']]['title'][0] ?? NULL;
          $doc['highlighting'] = $result['highlighting'][$doc['id']]['contents'] ?? NULL;
        }
        $rows[] = $doc;
      }

      foreach ($result['facet_counts']['facet_fields'] ?? [] as $key => $values) {
        $facetValues = [];
        while (count($values)) {
          [$k, $v] = array_splice($values, 0, 2);
          $facetValues[$k] = $v;
        }
        $facets[$key] = $facetValues;
      }
    }

    return [
      'numFound' => $numFound,
      'rows' => $rows,
      'facets' => $facets,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSuggestions(string $search): array {
    $query = [
      'q' => trim($search),
    ];

    if ($result = $this->getRequest('search', 'autocomplete?' . $this->buildSolrQuery($query))) {
      return $result['response']['docs'] ?? [];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function update(array $data) {
    return $this->postRequest('update', 'update?commit=true', [
      'body' => '[' . $this->json::encode($data) . ']',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(string $id) {
    return $this->postRequest('update', 'update?commit=true', [
      'body' => $this->json::encode(['delete' => ['id' => $id]]),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteQuery(array $query) {
    return $this->postRequest('update', 'update?commit=true', [
      'body' => $this->json::encode(['delete' => $query]),
    ]);
  }

  /**
   * Execute a post request and format the result.
   *
   * @param string $method
   *   The SOLR method. Should be either update or search.
   * @param string $action
   *   The HTTP action.
   * @param array $options
   *   The HTTP options.
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  protected function postRequest(string $method, string $action, array $options = []) {
    $options = array_merge_recursive($options, [
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ],
      'timeout' => 5,
    ]);

    $urlParts = [$this->config->get($method . '_host')];
    if ($path = $this->config->get($method . '_path')) {
      $urlParts[] = $path;
    }
    $urlParts[] = $this->config->get($method . '_' . $this->core . '_core');
    $urlParts[] = $action;

    return $this->execute('POST', implode('/', $urlParts), $options);
  }

  /**
   * Execute a get request and format the result.
   *
   * @param string $method
   *   The SOLR method. Should be either update or search.
   * @param string $action
   *   The HTTP action.
   * @param array $options
   *   The HTTP options.
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  protected function getRequest(string $method, string $action, array $options = []) {
    $options = array_merge_recursive($options, [
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ],
      'timeout' => 5,
    ]);

    $urlParts = [$this->config->get($method . '_host')];
    if ($path = $this->config->get($method . '_path')) {
      $urlParts[] = $path;
    }
    $urlParts[] = $this->config->get($method . '_' . $this->core . '_core');
    $urlParts[] = $action;

    return $this->execute('GET', implode('/', $urlParts), $options);
  }

  /**
   * Generate URL-encoded query string with default values for SOLR.
   *
   * @param array $queryData
   *   The query data.
   *
   * @return string
   *   A URL-encoded string.
   *
   * @see \http_build_query
   */
  protected function buildSolrQuery(array $queryData): ?string {
    if (!isset($queryData['wt'])) {
      $queryData['wt'] = 'json';
    }

    return http_build_query($queryData);
  }

  /**
   * Execute the HTTP request.
   *
   * @param string $method
   *   The HTTP method.
   * @param string $url
   *   The url.
   * @param array $options
   *   The HTTP options.
   *
   * @return mixed|false
   *   Returns the decoded response or FALSE on failure.
   */
  protected function execute(string $method, string $url, array $options) {
    try {
      // Add authentication if set.
      $username = $this->config->get('username');
      $password = $this->config->get('password');
      if ($username && $password) {
        $options['auth'] = [$username, $password];
      }

      if ($response = $this->client->request($method, $url, $options)) {
        return $this->json::decode($response->getBody());
      }
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->errors = [$message];
      if ($msg = $e->getResponse()->getBody()->getContents()) {
        $message = $msg;
      }
      $this->logger->error('Error: ' . $message);
    }

    return FALSE;
  }

}
