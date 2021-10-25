<?php

namespace Drupal\minfin_search\Controller;

use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\minfin_search\Entity\SolrSearchResult;
use Drupal\minfin_search\Form\SearchSortingFilterForm;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines the search page.
 */
class SearchController extends BaseSolrRequestController {

  public const FACET_LABELS = [
    'facet_component' => 'Component',
    'facet_content_type' => 'Onderdeel',
    'facet_document_type' => 'Documentsoort',
    'facet_fiscal_article' => 'Artikel',
    'facet_fiscal_chapter' => 'Hoofdstuk',
    'facet_fiscal_phase' => 'Begrotingsfase',
    'facet_fiscal_year' => 'Begrotingsjaar',
    'facet_format' => 'Formaat',
  ];

  /**
   * Render the searchpage.
   *
   * @param int|null $page
   *   The active page.
   * @param int|null $recordsPerPage
   *   Records shown per page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content(?int $page, ?int $recordsPerPage): array {
    $page = $page ?? 1;
    $recordsPerPage = ($recordsPerPage > 10 && $recordsPerPage < 200 ? $recordsPerPage : 10);
    $queryParams = $this->currentRequest->query->all();

    $search = $queryParams['search'] ?? NULL;
    unset($queryParams['search']);

    $sort = $this->getSortValue($queryParams);

    $activeFacets = [];
    foreach ($queryParams as $k => $v) {
      if (!is_array($v)) {
        $v = [$v];
      }
      $activeFacets[$k] = $v;
    }

    $result = $this->solrClient->search($page, $recordsPerPage, $search, $sort, $this->getType(), $activeFacets);
    if ($session = $this->currentRequest->getSession()) {
      $session->set('minfin_search.search_results', $result);
    }

    $rows = [];
    foreach ($result['rows'] ?? [] as $id => $values) {
      $rows[] = $this->buildSearchRow($id, $values);
    }

    // Store these values in the session so we can use them in the piwik module.
    $searchFilters = [];
    foreach ($activeFacets as $k => $v) {
      $label = self::FACET_LABELS[$k] ?? $k;
      $searchFilters[$label] = implode(', ', $v);
    }
    $this->currentRequest->getSession()->set('minfin_piwik.search', [
      'search_term' => $search,
      'search_page' => $page,
      'search_results' => $result['numFound'] ?? 0,
      'search_filters' => $searchFilters,
    ]);

    return [
      '#theme' => 'minfin_search',
      '#title' => $this->formatPlural($result['numFound'] ?? 0, '1 searchresult', '@count searchresults'),
      '#rows' => $rows,
      '#pagination' => $this->getPagination($result['numFound'] ?? 0, $page, $recordsPerPage, $search, $sort, $activeFacets),
      '#sorting' => $this->formBuilder()->getForm(SearchSortingFilterForm::class, $this->getRouteName(), $recordsPerPage, $search, $sort, $activeFacets),
      '#facets' => $this->getFacets($recordsPerPage, $search, $sort, $result['facets'] ?? [], $activeFacets),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Ajax callback to update a single row.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The ajax response.
   */
  public function updateSearchRow() {
    $build = [];
    $queryParams = $this->currentRequest->query->all();
    $id = (int) ($queryParams['search_row'] ?? 0);

    if ($session = $this->currentRequest->getSession()) {
      $result = $session->get('minfin_search.search_results');
      if (!empty($result['rows'][$id])) {
        $build = $this->buildSearchRow($id, $result['rows'][$id], $queryParams);
      }
    }

    $html = $this->renderer->renderRoot($build);
    $response = new Response();
    $response->setContent($html);

    return $response;
  }

  /**
   * Helper functoin to build a search row.
   *
   * @param int $id
   *   The id of the row.
   * @param array $values
   *   The values as returned by SOLR.
   * @param array $selectedValues
   *   An array with the current state of the select options.
   *
   * @return array
   *   A Drupal render array.
   */
  protected function buildSearchRow(int $id, array $values, array $selectedValues = []): array {
    $solrSearchResult = new SolrSearchResult($values);

    $build = $this->buildSearchRecord($id, $values, $selectedValues, TRUE);
    $build['#theme'] = 'minfin_search_record';
    $build['#summary'] = $solrSearchResult->getSummary();
    $build['#quickLinks'] = $solrSearchResult->getQuickLinks($selectedValues);

    return $build;
  }

  /**
   * Get the type of data we are searching on this searchpage.
   *
   * @return string
   *   The type.
   */
  protected function getType(): string {
    return 'all';
  }

  /**
   * Get the route name for the searchpage.
   *
   * @return string
   *   The route name.
   */
  protected function getRouteName(): string {
    return 'minfin_search.search';
  }

  /**
   * Get the default sorting.
   *
   * @return string
   *   The default sorting.
   */
  protected function getDefaultSort(): string {
    if ($this->currentRequest->query->get('search')) {
      return 'score desc';
    }
    return 'fiscal_year desc';
  }

  /**
   * Get the correct route name for the given facet_content_type value.
   *
   * @param string $value
   *   The facet value returned by SOLR.
   *
   * @return string
   *   The route name.
   */
  protected function getTypeFacetRouteName(string $value): string {
    switch ($value) {
      case 'Open data':
        return 'minfin_search.search.open_data';

      case 'RBV':
        return 'minfin_search.search.rbv';

      case 'Rijksbegroting':
        return 'minfin_search.search.rijksbegroting';
    }

    return 'minfin_search.search';
  }

  /**
   * Get the facets.
   *
   * @param int $recordsPerPage
   *   Records shown per page.
   * @param string|null $search
   *   The search value.
   * @param string|null $sort
   *   The sort value.
   * @param array $result
   *   The facets as returned by SOLR.
   * @param array $activeFacets
   *   The active facets.
   *
   * @return array
   *   A Drupal render array.
   */
  protected function getFacets(int $recordsPerPage, ?string $search = NULL, ?string $sort = NULL, array $result = [], array $activeFacets = []): array {
    $facets = [];

    // Generate the facet links.
    foreach ($result as $k => $facet) {
      $label = self::FACET_LABELS[$k] ?? $k;

      // Generate a remove link for active facets.
      if (isset($activeFacets[$k])) {
        foreach ($activeFacets[$k] as $delta => $v) {
          $tmpFilters = $activeFacets;
          unset($tmpFilters[$k][$delta]);
          $url = $this->buildSearchUrl($this->getRouteName(), 1, $recordsPerPage, $search, $sort, $tmpFilters, ['attributes' => ['class' => ['remove']]]);

          $facets[$label][$v] = [
            'link' => Link::fromTextAndUrl($v, $url),
            'count' => '',
          ];
        }
      }

      // Generate a add link for the rest.
      foreach ($facet as $value => $count) {
        if (!isset($facets[$label][$value])) {
          // We have to overwrite the URL for the facet_content_type as it needs
          // to redirect the user to a different search page.
          if ($k === 'facet_content_type') {
            $url = $this->buildSearchUrl($this->getTypeFacetRouteName($value), 1, $recordsPerPage, $search, $sort);
          }
          else {
            $tmpFilters = $activeFacets;
            $tmpFilters[$k][] = $value;
            $url = $this->buildSearchUrl($this->getRouteName(), 1, $recordsPerPage, $search, $sort, $tmpFilters);
          }

          $title = !empty($value) ? $value : 'Onbekend';
          $facets[$label][$value] = [
            'link' => Link::fromTextAndUrl($title, $url),
            'count' => $count,
          ];
        }
      }
    }

    foreach ($facets as &$values) {
      usort($values, static function ($a, $b) {
        // We want the higher number to be on top.
        return (int) $b['count'] <=> (int) $a['count'];
      });
    }

    return [
      '#theme' => 'minfin_facets',
      '#facets' => $facets,
    ];
  }

  /**
   * Get the pagination.
   *
   * @param int $numberOfRecords
   *   The amount of records found.
   * @param int $page
   *   The active page.
   * @param int $recordsPerPage
   *   Records shown per page.
   * @param string|null $search
   *   The search value.
   * @param string|null $sort
   *   The sort value.
   * @param array $activeFacets
   *   The active facets.
   *
   * @return array
   *   A Drupal render array.
   */
  protected function getPagination(int $numberOfRecords, int $page, int $recordsPerPage, ?string $search = NULL, ?string $sort = NULL, array $activeFacets = []): array {
    $links = [];

    if ($numberOfRecords === 0) {
      return $links;
    }

    $last = (int) ceil($numberOfRecords / $recordsPerPage);
    $start = (($page - 1) > 0) ? $page - 1 : 1;
    $end = (($page + 1) < $last) ? $page + 1 : $last;

    if ($page !== 1) {
      $links[] = [
        'type' => 'link',
        'url' => $this->buildSearchUrl($this->getRouteName(), $page - 1, $recordsPerPage, $search, $sort, $activeFacets),
        'label' => Markup::create('&laquo;'),
        'active' => FALSE,
        'disabled' => ($page === 1),
      ];
    }

    if ($start > 1) {
      $links[] = [
        'type' => 'link',
        'url' => $this->buildSearchUrl($this->getRouteName(), 1, $recordsPerPage, $search, $sort, $activeFacets),
        'label' => 1,
        'active' => FALSE,
        'disabled' => FALSE,
      ];
    }

    if ($start > 2) {
      $links[] = [
        'type' => 'separator',
      ];
    }

    for ($i = $start; $i <= $end; $i++) {
      $links[] = [
        'type' => 'link',
        'url' => $this->buildSearchUrl($this->getRouteName(), $i, $recordsPerPage, $search, $sort, $activeFacets),
        'label' => $i,
        'active' => ($page === $i),
        'disabled' => FALSE,
      ];
    }

    if (($last - $end) > 1) {
      $links[] = [
        'type' => 'separator',
      ];
    }

    if ($end < $last) {
      $links[] = [
        'type' => 'link',
        'url' => $this->buildSearchUrl($this->getRouteName(), $last, $recordsPerPage, $search, $sort, $activeFacets),
        'label' => $last,
        'active' => FALSE,
        'disabled' => FALSE,
      ];
    }

    if ($page !== $last) {
      $links[] = [
        'type' => 'link',
        'url' => $this->buildSearchUrl($this->getRouteName(), $page + 1, $recordsPerPage, $search, $sort, $activeFacets),
        'label' => Markup::create('&raquo;'),
        'active' => FALSE,
        'disabled' => ($page === $last),
      ];
    }

    return [
      '#theme' => 'minfin_pagination',
      '#pagination' => $links,
    ];
  }

  /**
   * Get the sort values from the query params array.
   *
   * @param array $queryParams
   *   Array containing all query params.
   *
   * @return string
   *   The sort value.
   */
  private function getSortValue(array &$queryParams): string {
    if (isset($queryParams['sort'])) {
      $sort = trim($queryParams['sort']);
      unset($queryParams['sort']);

      $safeValue = TRUE;
      foreach (explode(',', $sort) as $v) {
        if (substr($v, -5) !== ' desc' && substr($v, -4) !== ' asc') {
          $safeValue = FALSE;
        }
      }

      if ($safeValue) {
        return $sort;
      }
    }
    return $this->getDefaultSort();
  }

}
