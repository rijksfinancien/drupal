<?php

namespace Drupal\minfin;

use Drupal\Core\Url;
use Drupal\minfin_solr\SolrClientInterface;

/**
 * Defines the SOLR client used for synchronizing kamerstukken.
 */
class SolrKamerstukClient implements SolrKamerstukClientInterface {

  /**
   * The SOLR client.
   *
   * @var \Drupal\minfin_solr\SolrClientInterface
   */
  protected $solrClient;

  /**
   * The MinFin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingServiceInterface
   */
  protected $minfinNaming;

  /**
   * Constructs a SolrKamerstukClient object.
   *
   * @param \Drupal\minfin_solr\SolrClientInterface $solrClient
   *   The SOLR client.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNaming
   *   The MinFin naming service.
   */
  public function __construct(SolrClientInterface $solrClient, MinfinNamingServiceInterface $minfinNaming) {
    $this->solrClient = $solrClient;
    $this->minfinNaming = $minfinNaming;
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
  public function update(bool $appendix, string $type, string $phase, int $year, string $name, string $html, string $anchor, ?string $hoofdstukMinfinId, ?string $artikelMinfinId) {
    $documentType = $this->minfinNaming->getDocumentType($type, $phase);
    $component = $this->getComponent($type);
    $routeParams = ['year' => $year, 'anchor' => $anchor];
    $phaseTypes = [
      'memorie_van_toelichting',
      'voorstel_van_wet',
      'isb_voorstel_van_wet',
      'isb_memorie_van_toelichting',
    ];
    if (in_array($type, $phaseTypes)) {
      $routeParams['phase'] = $phase;
      $routeParams['hoofdstukMinfinId'] = $hoofdstukMinfinId;
    }
    elseif ($type === 'jaarverslag') {
      $routeParams['hoofdstukMinfinId'] = $hoofdstukMinfinId;
    }

    $url = NULL;
    try {
      if ($type !== 'undefined') {
        if ($type === 'voorstel_van_wet' || $type === 'isb_voorstel_van_wet') {
          $url = Url::fromRoute('minfin.memorie_van_toelichting.voorstel_van_wet', $routeParams)->toString();
        }
        else {
          $url = Url::fromRoute('minfin.' . $type . ($appendix ? '.appendix' : '') . '.anchor', $routeParams)->toString();
        }
      }
    }
    catch (\Exception $e) {
      $url = NULL;
    }

    $data = [
      'content_type' => 'Rijksbegroting',
      'document_type' => $documentType,
      'fiscal_phase' => $this->minfinNaming->getFiscalPhase($type, $phase),
      'component' => $component,
      'cuid' => $anchor,
      'id' => implode('-', array_filter([
        'Rijksbegroting',
        $documentType,
        $component,
        $year,
        $hoofdstukMinfinId,
        $anchor,
      ])),
      'page_url' => $url,
      'download_url' => NULL,
      'title' => $name,
      'contents' => $html,
      'fiscal_year' => $year,
      'publication_date' => date('Y-m-d\TH:i:s\Z'),
      'modification_date' => date('Y-m-d\TH:i:s\Z'),
      'fiscal_chapter_id' => $hoofdstukMinfinId,
      'fiscal_article_id' => $artikelMinfinId,
    ];

    if ($hoofdstukMinfinId) {
      $data['fiscal_chapter'] = $this->minfinNaming->getHoofdstukName($year, $hoofdstukMinfinId);

      if ($artikelMinfinId) {
        $data['fiscal_article'] = $this->minfinNaming->getArtikelName($year, $hoofdstukMinfinId, $artikelMinfinId);
      }
    }

    return $this->solrClient->update($data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(string $type, string $phase, int $year, string $anchor, ?string $hoofdstukMinfinId) {
    return $this->solrClient->delete(implode('-', array_filter([
      'Rijksbegroting',
      $this->minfinNaming->getDocumentType($type, $phase),
      $this->getComponent($type),
      $year,
      $hoofdstukMinfinId,
      $anchor,
    ])));
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->solrClient->deleteQuery(['query' => 'facet_content_type:Rijksbegroting']);
  }

  /**
   * Retrieve the component based on the type.
   *
   * @param string $type
   *   The type.
   *
   * @return string|null
   *   The component.
   */
  private function getComponent(string $type): ?string {
    switch ($type) {
      case 'memorie_van_toelichting':
        return 'Memorie van toelichting';

      case 'voorstel_van_wet':
        return 'Voorstel van wet';
    }
    return NULL;
  }

}
