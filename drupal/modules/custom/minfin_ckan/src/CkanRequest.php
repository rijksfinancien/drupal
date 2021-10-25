<?php

namespace Drupal\minfin_ckan;

use Drupal\minfin_ckan\Entity\Dataset;
use Drupal\minfin_ckan\Entity\Resource as CkanResource;
use Drupal\minfin_ckan\Entity\Tag;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use GuzzleHttp\Client;

/**
 * The Ckan request.
 */
class CkanRequest implements CkanRequestInterface {

  /**
   * The base url.
   *
   * @var string|null
   */
  private $baseUrl;

  /**
   * The ckan request config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * The error logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;

  /**
   * CkanRequest constructor.
   *
   * @param \GuzzleHttp\Client $httpClient
   *   The guzzle client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   *   The logging factory.
   */
  public function __construct(Client $httpClient, ConfigFactoryInterface $configFactory, LoggerChannelFactory $loggerChannelFactory) {
    $this->client = $httpClient;
    $this->logger = $loggerChannelFactory->get('minfin_ckan');

    // Set base URL (if exists in the config).
    if ($config = $configFactory->get('minfin_ckan.request.settings')) {
      $this->baseUrl = $config->get('url');
      $this->config = $config;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDatasets($title = '', $keyToLower = TRUE): array {
    $page = 1;
    $resultPerPage = 200;
    $sort = 'temporal_start desc';

    $result = $this->searchDatasets($page, $resultPerPage, $title, $sort);
    $datasets = $result['datasets'];
    $total = $result['count'];
    $count = $resultPerPage;

    while ($total >= $count) {
      $page++;
      $count += $resultPerPage;
      $datasets = array_merge($datasets, $this->searchDatasets($page, $resultPerPage, $title, $sort)['datasets']);
    }

    $values = [];
    foreach ($datasets as $dataset) {
      $title = $dataset->getTitle();
      $tab = NULL;

      $matches = [];
      preg_match('/\b19|20\d{2}\b/', $title, $matches);
      if (isset($matches[0])) {
        $title = preg_replace('/\s+/', ' ', trim(str_replace($matches[0], '', $title)));
        $tab = $matches[0];
      }

      $key = str_replace(['/'], [''], $title);
      if ($keyToLower) {
        $key = strtolower($key);
      }
      $values[$key]['title'] = $title;
      if ($tab) {
        $values[$key]['datasets']['tabs'][$tab][] = $dataset;
      }
      $values[$key]['datasets']['all'][] = $dataset;
      krsort($values[$key]['datasets']);
    }
    ksort($values);

    return $values;
  }

  /**
   * Search through all the datasests.
   *
   * @param int $page
   *   The page of records to show.
   * @param int $recordsPerPage
   *   The amount of records to return per page.
   * @param string $search
   *   The search term to filter the results with.
   * @param string $sort
   *   The field(s) to sort the results with.
   * @param array $activeFacets
   *   Any active facets to filter the results with.
   *
   * @return array
   *   Array with all the data required for building the search page.
   */
  private function searchDatasets($page, $recordsPerPage, $search = '', $sort = '', array $activeFacets = []): array {
    $count = 0;
    $datasets = [];

    $activeFacets['authority'][] = $this->config->get('data_owner');

    $fq = [];
    foreach ($activeFacets as $k => $values) {
      if (\is_array($values)) {
        foreach ($values as $v) {
          $fq[] = $k . ':"' . $v . '"';
        }
      }
    }
    if ($landingPage = $this->config->get('landing_page')) {
      $fq[] = 'url:' . $landingPage;
    }

    $query = [
      'q' => $search . (!empty($search) && !empty($fq) ? ' AND ' : '') . implode(' AND ', $fq),
      'sort' => $sort,
      'rows' => $recordsPerPage,
      'start' => $recordsPerPage * ($page - 1),
      'facet' => 'true',
      'facet.limit' => -1,
    ];

    if ($result = $this->execute('package_search?' . http_build_query($query))) {
      $count = $result->count;

      foreach ($result->results as $v) {
        $datasets[] = $this->resultToDataset($v);
      }
    }

    return [
      'count' => $count,
      'datasets' => $datasets,
    ];
  }

  /**
   * Transform the CKAN json response into a Dataset object.
   *
   * @param object $result
   *   The result object.
   *
   * @return \Drupal\minfin_ckan\Entity\Dataset
   *   A ckan dataset.
   */
  private function resultToDataset(object $result): Dataset {
    $dataset = new Dataset();

    $dataset->setId($result->id);
    $dataset->setOwnerOrg($result->owner_org);
    $dataset->setIdentifier($result->identifier);
    $dataset->setLanguage($result->language);
    $dataset->setAuthority($result->authority);
    $dataset->setPublisher($result->publisher);
    $dataset->setContactPointName($result->contact_point_name);
    $dataset->setName($result->name);
    $dataset->setTitle($result->title);
    $dataset->setNotes($result->notes);
    $dataset->setMetadataLanguage($result->metadata_language);
    $dataset->setMetadataModified($result->metadata_modified);
    $dataset->setTheme($result->theme);
    $dataset->setModified($result->modified);
    $dataset->setLicenseId($result->license_id);
    $dataset->setPrivate((bool) $result->private);
    $dataset->setHighValue(isset($result->high_value) && strtolower($result->high_value) === 'true');
    $dataset->setBaseRegister(isset($result->basis_register) && strtolower($result->basis_register) === 'true');
    $dataset->setReferenceData(isset($result->referentie_data) && strtolower($result->referentie_data) === 'true');
    $dataset->setNationalCoverage(isset($result->national_coverage) && strtolower($result->national_coverage) === 'true');

    $resources = [];
    if (!empty($result->resources)) {
      foreach ($result->resources as $v) {
        $resources[] = $this->resultToResource($v);
      }
    }
    $dataset->setResources($resources);

    $tags = [];
    if (!empty($result->tags)) {
      foreach ($result->tags as $v) {
        $tag = new Tag();
        $tag->setId($v->id);
        $tag->setName($v->name);
        $tags[] = $tag;
      }
    }
    $dataset->setTags($tags);

    $dataset->setAlternateIdentifier($result->alternate_identifier ?? []);
    $dataset->setSourceCatalog($result->source_catalog ?? NULL);
    $dataset->setContactPointAddress($result->contact_point_address ?? NULL);
    $dataset->setContactPointEmail($result->contact_point_email ?? NULL);
    $dataset->setContactPointPhone($result->contact_point_phone ?? NULL);
    $dataset->setContactPointWebsite($result->contact_point_website ?? NULL);
    $dataset->setContactPointTitle($result->contact_point_title ?? NULL);
    $dataset->setAccessRights($result->access_rights ?? NULL);
    $dataset->setUrl($result->url ?? NULL);
    $dataset->setConformsTo($result->conforms_to ?? []);
    $dataset->setRelatedResource($result->related_resource ?? []);
    $dataset->setSource($result->source ?? []);
    $dataset->setVersion($result->version ?? NULL);
    $dataset->setHasVersion($result->has_verison ?? []);
    $dataset->setIsVersionOf($result->is_version_of ?? []);
    $dataset->setLegalFoundationRef($result->legal_foundation_ref ?? NULL);
    $dataset->setLegalFoundationUri($result->legal_foundation_uri ?? NULL);
    $dataset->setLegalFoundationLabel($result->legal_foundation_label ?? NULL);
    $dataset->setFrequency($result->frequency ?? NULL);
    $dataset->setProvenance($result->provenance ?? []);
    $dataset->setSample($result->sample ?? []);
    $dataset->setSpatialScheme($result->spatial_scheme ?? []);
    $dataset->setSpatialValue($result->spatial_value ?? []);
    $dataset->setTemporalLabel($result->temporal_label ?? NULL);
    $dataset->setTemporalStart($result->temporal_start ?? NULL);
    $dataset->setTemporalEnd($result->temporal_end ?? NULL);
    $dataset->setDatasetStatus($result->dataset_status ?? NULL);
    $dataset->setDatePlanned($result->date_planned ?? NULL);
    $dataset->setVersionNotes($result->version_notes ?? []);
    $dataset->setIssued($result->issued ?? NULL);
    $dataset->setDocumentation($result->documentation ?? []);
    $dataset->setCommunities($result->communities ?? []);
    $dataset->setDatasetQuality($result->dataset_quality ?? NULL);
    $dataset->setCreatorUserId($result->creator_user_id ?? NULL);

    return $dataset;
  }

  /**
   * Transform the CKAN json response into a Resource object.
   *
   * @param object $result
   *   The result object.
   *
   * @return \Drupal\minfin_ckan\Entity\Resource
   *   A ckan resource.
   */
  private function resultToResource(object $result): CkanResource {
    $resource = new CkanResource();

    $resource->setId($result->id);
    $resource->setPackageId($result->package_id);
    $resource->setUrl($result->url);
    $resource->setName($result->name);
    $resource->setDescription($result->description);
    $resource->setMetadataLanguage($result->metadata_language);
    $resource->setLanguage((is_array($result->language) ? $result->language : [$result->language]));
    $resource->setFormat($result->format);
    $resource->setLicenseId($result->license_id);

    $resource->setSize($result->size ?? NULL);
    $resource->setDownloadUrl($result->download_url ?? NULL);
    $resource->setMimetype($result->mimetype ?? NULL);
    $resource->setReleaseDate($result->release_date ?? NULL);
    $resource->setRights($result->rights ?? NULL);
    $resource->setStatus($result->status ?? NULL);
    $resource->setLinkStatus(isset($result->link_status) && $result->link_status == 1);
    $resource->setLinkStatusLastChecked($result->link_status_last_checked ?? NULL);
    $resource->setModificationDate($result->modification_date ?? NULL);
    $resource->setLinkedSchemas($result->linked_schemas ?? NULL);
    $resource->setHash($result->hash ?? NULL);
    $resource->setHashAlgorithm($result->hash_algorithm ?? NULL);
    $resource->setDocumentation($result->documentation ?? NULL);
    $resource->setCreated($result->created ?? NULL);

    return $resource;
  }

  /**
   * Execute the request and format the result.
   *
   * @param string $action
   *   The ckan action to call against.
   * @param array $options
   *   Additional guzzle client options.
   *
   * @return mixed|false
   *   Returns the decoded json response on success or FALSE on failure.
   */
  private function execute($action, array $options = []) {
    if (!$this->baseUrl) {
      $this->logger->error('No CKAN url found.');
      return FALSE;
    }

    try {
      $options = array_merge_recursive($options, [
        'headers' => [
          'Accept' => 'application/json',
        ],
        'timeout' => 5,
      ]);
      $url = $this->baseUrl . $action;

      $response = json_decode($this->client->get($url, $options)->getBody(), FALSE);
    }
    catch (\Exception $e) {
      if ($errorResponse = $e->getResponse()) {
        if ($errorResponse->getStatusCode() === 404) {
          $this->logger->warning($e->getMessage());
        }
        else {
          $result = json_decode($errorResponse->getBody()->getContents(), FALSE);
          if (isset($result->error)) {
            $error = (array) $result->error;
            unset($error['__type']);
            $this->logger->error(json_encode($error));
          }
        }
      }
      else {
        $this->logger->error($e->getMessage());
      }
      return FALSE;
    }

    // Check if the call was successful according to CKAN.
    if (isset($response->success, $response->result) && $response->success) {
      return $response->result;
    }

    if (isset($response->error)) {
      $error = (array) $response->error;
      unset($error['__type']);
      $this->logger->error(json_encode($error));
    }

    return FALSE;
  }

}
