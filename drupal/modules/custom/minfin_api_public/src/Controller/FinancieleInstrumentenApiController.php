<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
namespace Drupal\minfin_api_public\Controller;

use Drupal\minfin_api\Controller\FinancieleInstrumentenApiController as FinancieleInstrumentenPrivateApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * The swagger API for the Financiele instrumenten.
 */
class FinancieleInstrumentenApiController extends FinancieleInstrumentenPrivateApiController {

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/v2/financiele_instrumenten",
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
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   ),
   * )
   */
  public function json(): JsonResponse {
    return parent::json();
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/v2/financiele_instrumenten/artikelen",
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
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   ),
   * )
   */
  public function jsonArtikelen(): JsonResponse {
    return parent::jsonArtikelen();
  }

  /**
   * @see jsonArtikelen()
   */
  public function csvArtikelen(): Response {
    $data = [];
    $result = $this->getItems('artikel');
    foreach ($result['result'] as $item) {
      $item['jaar'] = $result['jaar'];
      $data[] = $item;
    }

    return $this->csvResponse('financiele_instrumenten', $data);
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/v2/financiele_instrumenten/hoofdstukken",
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
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   ),
   * )
   */
  public function jsonHoofdstukken(): JsonResponse {
    return parent::jsonHoofdstukken();
  }

  /**
   * @see jsonHoofdstukken()
   */
  public function csvHoofdstukken(): Response {
    $data = [];
    $result = $this->getItems('hoofdstuk');
    foreach ($result['result'] as $item) {
      $item['jaar'] = $result['jaar'];
      $data[] = $item;
    }

    return $this->csvResponse('financiele_instrumenten', $data);
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/v2/financiele_instrumenten/regelingen",
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
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   ),
   * )
   */
  public function jsonRegelingen(): JsonResponse {
    return parent::jsonRegelingen();
  }

  /**
   * @see jsonRegelingen()
   */
  public function csvRegelingen(): Response {
    $data = [];
    $result = $this->getItems('regeling');
    foreach ($result['result'] as $item) {
      $item['jaar'] = $result['jaar'];
      $data[] = $item;
    }

    return $this->csvResponse('financiele_instrumenten', $data);
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/v2/financiele_instrumenten/ontvangers",
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
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   ),
   * )
   */
  public function jsonOntvangers(): JsonResponse {
    return parent::jsonOntvangers();
  }

  /**
   * @see jsonOntvangers()
   */
  public function csvOntvangers(): Response {
    $data = [];
    $result = $this->getItems('ontvanger');
    foreach ($result['result'] as $item) {
      $item['jaar'] = $result['jaar'];
      $data[] = $item;
    }

    return $this->csvResponse('financiele_instrumenten', $data);
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/v2/financiele_instrumenten/available_years",
   *   summary = "Get the available years for the 'Wie ontvingen' chart.",
   *   description = "Get the available years for the 'Wie ontvingen' chart.",
   *   operationId = "WieOntvingenAvailableYears",
   *   tags = { "Financiele instrumenten" },
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   ),
   * )
   */
  public function getAvailableYears(): JsonResponse {
    return parent::getAvailableYears();
  }

}
