<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The swagger API for the Financiele instrumenten.
 */
class FinancieleInstrumentenApiController extends BaseApiController {

  const LIMIT = 25;

  /**
   * @SWG\Get(
   *   path = "/json/financiele_instrumenten",
   *   summary = "Json data for the 'Wie ontvingen' chart",
   *   description = "Json data for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingen",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Parameter(
   *     name = "referrer",
   *     in = "query",
   *     required = true,
   *     type = "string",
   *     enum={"artikel", "hoofdstuk", "ontvanger", "regeling"},
   *   ),
   *   @SWG\Parameter(
   *     name = "referrer_id",
   *     description = "The id of the referrer.",
   *     in = "query",
   *     required = true,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "type",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"artikel", "hoofdstuk", "ontvanger", "regeling"},
   *   ),
   *   @SWG\Parameter(
   *     name = "jaar",
   *     description = "The calendar year.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "search",
   *     description = "Search on the title.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "sort",
   *     description = "The sorting to use.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"amount desc", "amount asc", "name desc", "name asc", "score desc", "score asc"},
   *   ),
   *   @SWG\Parameter(
   *     name = "min",
   *     description = "The minimum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "max",
   *     description = "The maximum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "limit",
   *     description = "The amount or records to return, defaults to 25. Max is also 25",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   * )
   */
  public function json(): JsonResponse {
    $allowedValues = [
      'artikel',
      'hoofdstuk',
      'ontvanger',
      'regeling',
    ];

    $queryParams = $this->request->query->all();
    $years = $this->getYears();
    $year = (int) ($queryParams['jaar'] ?? max($years));

    $referrer = NULL;
    if (isset($queryParams['referrer']) && in_array($queryParams['referrer'], $allowedValues)) {
      $referrer = $queryParams['referrer'];
    }

    $referrerId = NULL;
    if (!empty($queryParams['referrer_id'])) {
      $referrerId = $queryParams['referrer_id'];
    }

    if (!$year || !$referrer || !$referrerId) {
      return $this->jsonResponse([]);
    }

    $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : $this::LIMIT;
    if ($limit > $this::LIMIT) {
      $limit = $this::LIMIT;
    }

    $search = $queryParams['search'] ?? NULL;
    $sort = $this->getSortValue($queryParams);

    $min = isset($queryParams['min']) ? (int) $queryParams['min'] : '*';
    $max = isset($queryParams['max']) ? (int) $queryParams['max'] : '*';

    $type = ($referrer !== 'ontvanger' ? 'ontvanger' : 'regeling');
    if (isset($queryParams['type']) && in_array($queryParams['type'], $allowedValues)) {
      $type = $queryParams['type'];
    }

    // Get base data.
    $facets = [
      'year' => [$year],
      'type' => [$referrer],
      'grouped_by' => [$referrerId],
      'grouped_by_type' => [$referrer],
    ];
    $this->solrClient->setCore('wie_ontvingen');
    $result = $this->solrClient->search(1, 1, NULL, $sort, 'all', $facets)['rows'][0];

    $data = [
      'type' => $type,
      'referrer' => $referrer,
      'referrer_id' => $referrerId,
      'year' => $year,
      'title' => $result['name'],
      'total' => $result['amount'],
      'max' => 0,
      'min' => 0,
      'total_results' => 0,
      'records_show' => $limit,
      'result' => [],
    ];

    // Get min & max.
    // @todo check if we can do this without additional SOLR calls.
    $facets = [
      'year' => [$year],
      'type' => [$type],
      'grouped_by' => [$referrerId],
      'grouped_by_type' => [$referrer],
    ];
    $this->solrClient->setCore('wie_ontvingen');
    $data['max'] = $this->solrClient->search(1, 1, $search, 'amount desc', 'all', $facets)['rows'][0]['amount'] ?? 0;
    $data['min'] = $this->solrClient->search(1, 1, $search, 'amount asc', 'all', $facets)['rows'][0]['amount'] ?? 0;

    // Get the records.
    $facets = [
      'year' => [$year],
      'type' => [$type],
      'grouped_by' => [$referrerId],
      'grouped_by_type' => [$referrer],
      'amount' => ['[' . $min . ' TO ' . $max . ']'],
    ];
    $this->solrClient->setCore('wie_ontvingen');
    $result = $this->solrClient->search(1, $limit, $search, 'amount desc', 'all', $facets);

    $data['total_results'] = $result['numFound'];
    foreach ($result['rows'] ?? [] as $values) {
      $data['result'][] = [
        'id' => $values['name'],
        'title' => $values['name'],
        'amount' => $values['amount'],
      ];
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/financiele_instrumenten/artikelen",
   *   summary = "List of available 'artikelen' for the 'Wie ontvingen' chart",
   *   description = "List of available 'artikelen' for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingenOntvangers",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Parameter(
   *     name = "jaar",
   *     description = "The calendar year.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "search",
   *     description = "Search on the title.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "sort",
   *     description = "The sorting to use.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"amount desc", "amount asc", "name desc", "name asc", "score desc", "score asc"},
   *   ),
   *   @SWG\Parameter(
   *     name = "min",
   *     description = "The minimum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "max",
   *     description = "The maximum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   * )
   */
  public function jsonArtikelen(): JsonResponse {
    return $this->jsonResponse($this->getItems('artikel'));
  }

  /**
   * @SWG\Get(
   *   path = "/json/financiele_instrumenten/hoofdstukken",
   *   summary = "List of available 'hoofdstukken' for the 'Wie ontvingen' chart",
   *   description = "List of available 'hoofdstukken' for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingenOntvangers",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Parameter(
   *     name = "jaar",
   *     description = "The calendar year.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "search",
   *     description = "Search on the title.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "sort",
   *     description = "The sorting to use.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"amount desc", "amount asc", "name desc", "name asc", "score desc", "score asc"},
   *   ),
   *   @SWG\Parameter(
   *     name = "min",
   *     description = "The minimum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "max",
   *     description = "The maximum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   * )
   */
  public function jsonHoofdstukken(): JsonResponse {
    return $this->jsonResponse($this->getItems('hoofdstuk'));
  }

  /**
   * @SWG\Get(
   *   path = "/json/financiele_instrumenten/regelingen",
   *   summary = "List of available 'regelingen' for the 'Wie ontvingen' chart",
   *   description = "List of available 'regelingen' for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingenRegeling",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Parameter(
   *     name = "jaar",
   *     description = "The calendar year.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "search",
   *     description = "Search on the title.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "sort",
   *     description = "The sorting to use.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"amount desc", "amount asc", "name desc", "name asc", "score desc", "score asc"},
   *   ),
   *   @SWG\Parameter(
   *     name = "min",
   *     description = "The minimum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "max",
   *     description = "The maximum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   * )
   */
  public function jsonRegelingen(): JsonResponse {
    return $this->jsonResponse($this->getItems('regeling'));
  }

  /**
   * @SWG\Get(
   *   path = "/json/financiele_instrumenten/ontvangers",
   *   summary = "List of available 'ontvangers' for the 'Wie ontvingen' chart",
   *   description = "List of available 'ontvangers' for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingenOntvangers",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Parameter(
   *     name = "jaar",
   *     description = "The calendar year.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "search",
   *     description = "Search on the title.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "sort",
   *     description = "The sorting to use.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"amount desc", "amount asc", "name desc", "name asc", "score desc", "score asc"},
   *   ),
   *   @SWG\Parameter(
   *     name = "min",
   *     description = "The minimum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "max",
   *     description = "The maximum amount.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   * )
   */
  public function jsonOntvangers(): JsonResponse {
    return $this->jsonResponse($this->getItems('ontvanger'));
  }

  /**
   * @SWG\Get(
   *   path = "/json/financiele_instrumenten/available_years",
   *   summary = "Get the available years for the 'Wie ontvingen' chart.",
   *   description = "Get the available years for the 'Wie ontvingen' chart.",
   *   operationId = "WieOntvingenAvailableYears",
   *   tags = { "Financiele instrumenten" },
   * )
   */
  public function getAvailableYears(): JsonResponse {
    return $this->jsonResponse($this->getYears());
  }

  /**
   * Get a list of items.
   *
   * @param string $type
   *   The type.
   *
   * @return array
   *   The requested items.
   */
  protected function getItems(string $type): array {
    $queryParams = $this->request->query->all();
    $year = isset($queryParams['jaar']) ? (int) $queryParams['jaar'] : NULL;

    $search = $queryParams['search'] ?? NULL;
    $sort = $this->getSortValue($queryParams);

    $min = isset($queryParams['min']) ? (int) $queryParams['min'] : '*';
    $max = isset($queryParams['max']) ? (int) $queryParams['max'] : '*';

    $facets = [
      'type' => [$type],
      'grouped_by_type' => [$type],
      'amount' => ['[' . $min . ' TO ' . $max . ']'],
    ];
    if ($year) {
      $facets['year'] = [$year];
    }

    $this->solrClient->setCore('wie_ontvingen');
    $result = $this->solrClient->search(1, $this::LIMIT, $search, $sort, 'all', $facets);

    $data = [
      'jaar' => $year,
      'search' => $search,
      'total_results' => $result['numFound'],
      'result' => [],
    ];
    foreach ($result['rows'] ?? [] as $row) {
      $values = [
        'id' => $row['grouped_by'],
        'titel' => $row['name'],
        'bedrag' => $row['amount'],
      ];
      if (!$year) {
        $values['jaar'] = $row['year'];
      }

      $data['result'][] = $values;
    }

    return $data;
  }

  /**
   * Get the available years.
   *
   * @return array
   *   The years.
   */
  protected function getYears(): array {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->distinct();
    $query->fields('fi', ['jaar']);
    $query->orderBy('fi.jaar', 'ASC');
    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Get the sort value from the query params array.
   *
   * @param array $queryParams
   *   Array containing all active query params.
   *
   * @return string
   *   The sort value.
   */
  protected function getSortValue(array &$queryParams): string {
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
    return 'amount desc';
  }

}
