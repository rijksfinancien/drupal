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

    $search = $queryParams['search'] ?? NULL;
    $min = ((int) $queryParams['min']) ?: '*';
    $max = ((int) $queryParams['max']) ?: '*';

    $type = (isset($queryParams['type']) ? 'ontvanger' : NULL);
    if (isset($queryParams['type']) && in_array($queryParams['type'], $allowedValues)) {
      $type = $queryParams['type'];
    }

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

    $facets = [
      'year' => [$year],
      'type' => [$type],
      'grouped_by' => $referrerId,
      'grouped_by_type' => [$referrer],
      'amount' => ['[' . $min . ' TO ' . $max . ']'],
    ];

    $this->solrClient->setCore('wie_ontvingen');
    $result = $this->solrClient->search(1, $this::LIMIT, $search, 'amount desc', 'all', $facets);

    $data = [
      'type' => $type,
      'referrer' => $referrer,
      'referrer_id' => $referrerId,
      'jaar' => $year,
      'total_results' => $result['numFound'],
      'result' => [],
    ];
    foreach ($result['rows'] ?? [] as $values) {
      $data['result'][] = [
        'id' => $values['grouped_by'],
        'titel' => $values['name'],
        'bedrag' => $values['amount'],
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
   * Get a list of items.
   *
   * @param string $type
   *   The type.
   *
   * @return array
   *   The requested items.
   */
  private function getItems(string $type): array {
    $queryParams = $this->request->query->all();
    $years = $this->getYears();
    $year = (int) ($queryParams['jaar'] ?? max($years));

    $search = $queryParams['search'] ?? NULL;
    $min = ((int) $queryParams['min']) ?: '*';
    $max = ((int) $queryParams['max']) ?: '*';

    $facets = [
      'year' => [$year],
      'type' => [$type],
      'grouped_by_type' => [$type],
      'amount' => ['[' . $min . ' TO ' . $max . ']'],
    ];

    $this->solrClient->setCore('wie_ontvingen');
    $result = $this->solrClient->search(1, $this::LIMIT, $search, 'amount desc', 'all', $facets);

    $data = [
      'jaar' => $year,
      'search' => $search,
      'total_results' => $result['numFound'],
      'result' => [],
    ];
    foreach ($result['rows'] ?? [] as $values) {
      $data['result'][] = [
        'id' => $values['grouped_by'],
        'titel' => $values['name'],
        'bedrag' => $values['amount'],
      ];
    }

    return $data;
  }

  /**
   * Get the available years.
   *
   * @return array
   *   The years.
   */
  private function getYears(): array {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->distinct();
    $query->fields('fi', ['jaar']);
    $query->orderBy('fi.jaar', 'ASC');
    return $query->execute()->fetchAllKeyed(0, 0);
  }

}
