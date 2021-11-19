<?php

namespace Drupal\minfin;

use Drupal\minfin_solr\SolrClientInterface;

/**
 * Defines the SOLR client used for synchronizing the 'Wie ontvingen' visual.
 */
class SolrWieOntvingenClient implements SolrWieOntvingenClientInterface {

  /**
   * The SOLR client.
   *
   * @var \Drupal\minfin_solr\SolrClientInterface
   */
  protected $solrClient;

  /**
   * Constructs a SolrKamerstukClient object.
   *
   * @param \Drupal\minfin_solr\SolrClientInterface $solrClient
   *   The SOLR client.
   */
  public function __construct(SolrClientInterface $solrClient) {
    $this->solrClient = $solrClient;
    $this->solrClient->setCore('wie_ontvingen');
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors(): array {
    return $this->solrClient->getErrors();
  }

  /**
   * {@inheritdoc}
   */
  public function update(array $data, bool $commit = FALSE) {
    return $this->solrClient->update($data, $commit);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll(int $year) {
    $this->solrClient->deleteQuery(['query' => 'year:' . $year]);
  }

}
