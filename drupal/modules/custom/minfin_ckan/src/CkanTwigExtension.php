<?php

namespace Drupal\minfin_ckan;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;

/**
 * Contains the twig extension.
 */
class CkanTwigExtension extends \Twig_Extension {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheBackend;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * The guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  private $httpclient;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;

  /**
   * The value list location.
   *
   * @var string
   */
  private $valueListLocation;

  /**
   * CkanTwigExtension constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   * @param \GuzzleHttp\Client $httpclient
   *   The guzzle client.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger factory.
   */
  public function __construct(CacheBackendInterface $cacheBackend, Client $httpclient, DateFormatterInterface $dateFormatter, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->cacheBackend = $cacheBackend;
    $this->dateFormatter = $dateFormatter;
    $this->httpclient = $httpclient;
    $this->logger = $loggerChannelFactory->get('minfin_ckan');

    $this->valueListLocation = 'https://beta2-acc-data.overheid.nl/service/waardelijsten/';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('ckan_format_date', [$this, 'formatDate']),
      new \Twig_SimpleFunction('ckan_get_status_name', [$this, 'getStatusName']),
      new \Twig_SimpleFunction('ckan_get_license_name', [$this, 'getLicenseName']),
      new \Twig_SimpleFunction('ckan_get_language_name', [$this, 'getLanguageName']),
      new \Twig_SimpleFunction('ckan_get_file_format_name', [$this, 'getFileFormatName']),
      new \Twig_SimpleFunction('ckan_get_data_owner_name', [$this, 'getDataOwnerName']),
    ];
  }

  /**
   * Format the date.
   *
   * @param string $value
   *   The date string to format.
   *
   * @return string
   *   The formated value.
   */
  public function formatDate($value) {
    if (empty($value)) {
      return '';
    }

    preg_match('/^(\d*)-(\d*)-(\d{2})/', $value, $matches);
    $timestamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    return $this->dateFormatter->format($timestamp, 'short');
  }

  /**
   * Get status name.
   *
   * @param string $uri
   *   The uri.
   *
   * @return string
   *   The name for the given uri.
   */
  public function getStatusName($uri) {
    $list = $this->getValueList('dcatapdonl_overheid_dataset_stat');
    return $list[$uri] ?? $uri;
  }

  /**
   * Get license name.
   *
   * @param string $uri
   *   The uri.
   *
   * @return string
   *   The name for the given uri.
   */
  public function getLicenseName($uri) {
    $list = $this->getValueList('dcatapdonl_overheid_license');
    return $list[$uri] ?? $uri;
  }

  /**
   * Get status name.
   *
   * @param string $uri
   *   The uri.
   *
   * @return string
   *   The name for the given uri.
   */
  public function getLanguageName($uri) {
    $list = $this->getValueList('dcatapdonl_donl_language');
    return $list[$uri] ?? $uri;
  }

  /**
   * Get file format name.
   *
   * @param string $uri
   *   The uri.
   *
   * @return string
   *   The name for the given uri.
   */
  public function getFileFormatName($uri) {
    $list = $this->getValueList('dcatapdonl_mdr_filetype');
    return $list[$uri] ?? $uri;
  }

  /**
   * Get owner name.
   *
   * @param string $uri
   *   The uri.
   *
   * @return string
   *   The name for the given uri.
   */
  public function getDataOwnerName($uri) {
    $list = $this->getValueList('dcatapdonl_donl_organization');
    return $list[$uri] ?? $uri;
  }

  /**
   * Retrieve the value list data.
   *
   * @param string $list
   *   The name of the value list.
   *
   * @return array
   *   The value list.
   */
  private function getValueList($list) {
    $cid = 'minfin_ckan:' . $list;
    $values = [];

    // Check if we can get the values out of the cache.
    $cache = $this->cacheBackend->get($cid);
    if ($cache && $cache->valid) {
      $values = $cache->data;
    }

    if (!$values) {
      try {
        $options = [
          'headers' => [
            'Accept' => 'application/json',
          ],
          'timeout' => 5,
        ];

        $response = $this->httpclient->get($this->valueListLocation . $list, $options);
        foreach (json_decode($response->getBody(), TRUE) as $item) {
          if (isset($item['identifier']) && isset($item['label_nl'])) {
            $values[$item['identifier']] = $item['label_nl'];
          }
        }

        if ($values) {
          $this->cacheBackend->set($cid, $values, strtotime('+7 day'));
        }
      }
      catch (\Exception $e) {
        $this->logger->error('Failed to retrieve the value list. @error', ['@error' => $e->getMessage()]);
      }
    }

    return $values;
  }

}
