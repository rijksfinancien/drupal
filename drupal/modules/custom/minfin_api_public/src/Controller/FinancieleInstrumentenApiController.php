<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
namespace Drupal\minfin_api_public\Controller;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\minfin_api\Controller\FinancieleInstrumentenOldApiController as FinancieleInstrumentenPrivateApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The swagger API for the Financiele instrumenten.
 */
class FinancieleInstrumentenApiController extends FinancieleInstrumentenPrivateApiController {

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/financiele_instrumenten",
   *   summary = "Json data for the 'Wie ontvingen' chart",
   *   description = "Json data for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingen",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Parameter(
   *     name = "year[]",
   *     description = "The calendar years.",
   *     in = "query",
   *     required = true,
   *     type = "array",
   *     collectionFormat = "multi",
   *     @SWG\Items(type="integer"),
   *   ),
   *   @SWG\Parameter(
   *     name = "regulation[]",
   *     description = "The name of the regulation. You can use '%' as a wildcard.",
   *     in = "query",
   *     required = false,
   *     type = "array",
   *     collectionFormat = "multi",
   *     @SWG\Items(type="string"),
   *   ),
   *   @SWG\Parameter(
   *     name = "receiver[]",
   *     description = "The name of the receiver. You can use '%' as a wildcard.",
   *     in = "query",
   *     required = false,
   *     type = "array",
   *     collectionFormat = "multi",
   *     @SWG\Items(type="string"),
   *   ),
   *   @SWG\Parameter(
   *     name = "instrument[]",
   *     description = "The name of the instrument. You can use '%' as a wildcard.",
   *     in = "query",
   *     required = false,
   *     type = "array",
   *     collectionFormat = "multi",
   *     @SWG\Items(type="string"),
   *   ),
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   )
   * )
   */
  public function json(): JsonResponse {
    return parent::json();
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/financiele_instrumenten/instruments",
   *   summary = "List of available instruments for the 'Wie ontvingen' chart",
   *   description = "List of available instruments for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingenInstruments",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   )
   * )
   */
  public function jsonInstruments(): JsonResponse {
    return parent::jsonInstruments();
  }

  /**
   * Add the filters to the select query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The select query.
   * @param array $filters
   *   The filters.
   */
  protected function addQueryFilters(SelectInterface $query, array $filters): void {
    if (!empty($filters['instrument'])) {
      $query->condition('fi.instrument', $filters['instrument'], 'LIKE');
    }
    if (!empty($filters['receiver'])) {
      $query->condition('fi.ontvanger', $filters['receiver'], 'LIKE');
    }
    if (!empty($filters['regulation'])) {
      $query->condition('fi.regeling', $filters['regulation'], 'LIKE');
    }
  }

}
