<?php

namespace Drupal\minfin_search\Entity;

/**
 * Defines a SOLR result.
 */
class SolrResult implements SolrResultInterface {

  /**
   * The values as returned by SOLR.
   *
   * @var array
   */
  public $values;

  /**
   * The minfin service.
   *
   * @var \Drupal\minfin\MinfinService
   */
  protected $minfinService;

  /**
   * The minfin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingService
   */
  protected $minfinNamingService;

  /**
   * SearchResult constructor.
   *
   * @param array $values
   *   The values as returned by SOLR.
   */
  public function __construct(array $values) {
    $this->values = $values;
    $this->minfinService = \Drupal::service('minfin.minfin');
    $this->minfinNamingService = \Drupal::service('minfin.naming');
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return $this->values['highlighting_title'] ?? $this->values['title'] ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function getYears(): array {
    return $this->getMultivalue('fiscal_year');
  }

  /**
   * Helper function to retreive a multi value.
   *
   * @param string $key
   *   The key of the values in the the values array.
   *
   * @return array
   *   The values.
   */
  protected function getMultivalue(string $key): array {
    $array = [];
    if (!empty($this->values[$key])) {
      if (is_array($this->values[$key])) {
        foreach ($this->values[$key] as $v) {
          $array[$v] = $v;
        }
      }
      else {
        $array = [$this->values[$key] => $this->values[$key]];
      }
    }
    return $array;
  }

  /**
   * {@inheritdoc}
   */
  public function getDocumentTypes(): array {
    return $this->getMultivalue('document_type');
  }

  /**
   * {@inheritdoc}
   */
  public function getPhases(): array {
    return $this->getMultivalue('fiscal_phase');
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(array $selectedValues = []): string {
    if (!$url = $this->values['page_url']) {
      return '';
    }

    switch ($this->getType()) {
      case 'Rijksbegroting':
        // If $selectedValues are empty we don't need to manipulate the URL.
        if (!$selectedValues) {
          return $url;
        }

        $urlParts = explode('/', $url);

        // From the first value we can get the type.
        $kamerstukType = str_replace('-', '_', $urlParts[1]);

        // The second value is always the year.
        $year = $urlParts[2];
        if (!empty($selectedValues['year'])) {
          $year = $selectedValues['year'];
        }

        // The remaining values are based on the different routes.
        $phase = NULL;
        $hoofdstukMinfinId = NULL;
        $artikelMinfinId = NULL;
        switch ($kamerstukType) {
          case 'memorie_van_toelichting':
          case 'voorstel_van_wet':
            $phase = $urlParts[3];
            $hoofdstukMinfinId = $urlParts[4] ?? NULL;
            if ($phase && $hoofdstukMinfinId && !empty($urlParts[6])) {
              $artikelMinfinId = $this->minfinService->getArtikelMinfinIdFromKamerstukAnchor($kamerstukType, (int) $urlParts[2], $phase, $hoofdstukMinfinId, $urlParts[6]);
            }
            break;

          case 'jaarverslag':
            $phase = 'JV';
            $hoofdstukMinfinId = $urlParts[3] ?? NULL;
            if ($phase && $hoofdstukMinfinId && !empty($urlParts[5])) {
              $artikelMinfinId = $this->minfinService->getArtikelMinfinIdFromKamerstukAnchor($kamerstukType, (int) $urlParts[2], $phase, $hoofdstukMinfinId, $urlParts[5]);
            }
            break;

          case 'miljoenennota':
          case 'belastingplan_memorie_van_toelichting':
          case 'belastingplan_voorstel_van_wet':
          case 'belastingplan_staatsblad':
          case 'voorjaarsnota':
          case 'najaarsnota':
          case 'financieel_jaarverslag':
            switch ($kamerstukType) {
              case 'miljoenennota':
              case 'belastingplan_memorie_van_toelichting':
              case 'belastingplan_voorstel_van_wet':
              case 'belastingplan_staatsblad':
                $phase = 'OWB';
                break;

              case 'voorjaarsnota':
                $phase = '1SUPP';
                break;

              case 'najaarsnota':
                $phase = '2SUPP';
                break;

              case 'financieel_jaarverslag':
                $phase = 'JV';
                break;
            }

            if ($phase && !empty($urlParts[3])) {
              $artikelMinfinId = $this->minfinService->getArtikelMinfinIdFromKamerstukAnchor($kamerstukType, (int) $urlParts[2], $phase, NULL, $urlParts[3]);
            }
            break;
        }

        if (isset($selectedValues['document_type'])) {
          $kamerstukType = $this->minfinService->getTypeByDocumentType($selectedValues['document_type']) ?? $kamerstukType;
          $phase = $this->minfinService->getPhaseByDocumentType($selectedValues['document_type']) ?? $phase;
        }
        if ($kamerstukUrl = $this->minfinService->buildKamerstukUrl('minfin.' . $kamerstukType, $year, [], $phase, $hoofdstukMinfinId, $artikelMinfinId)) {
          if ($kamerstukUrl->access()) {
            return $kamerstukUrl->toString();
          }
        }
        break;

      case 'RBV':
        return \Drupal::config('minfin_search.settings')->get('rbv_url') . $url;

      case 'Open data':
        return $url;
    }

    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->values['content_type'] ?? 'Unknown';
  }

  /**
   * Helper function to get the visual phase name for the given document type.
   *
   * @param string|null $documentType
   *   The document type.
   *
   * @return string|null
   *   The phase name.
   */
  protected function documentTypeToVisualPhase(?string $documentType): ?string {
    switch ($documentType) {
      case 'Begroting':
        return 'begroting';

      case 'Jaarverslag':
        return 'jaarverslag';
    }

    return NULL;
  }

}
