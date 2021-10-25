<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

use Drupal\Core\Database\Query\SelectInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The swagger API for the Financiele instrumenten.
 */
class FinancieleInstrumentenOldApiController extends BaseApiController {

  /**
   * @SWG\Get(
   *   path = "/json/old/financiele_instrumenten",
   *   summary = "Json data for the 'Wie ontvingen' chart",
   *   description = "Json data for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingen",
   *   tags = { "Financiele instrumenten (old)" },
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
   *     description = "The name of the regulation.",
   *     in = "query",
   *     required = false,
   *     type = "array",
   *     collectionFormat = "multi",
   *     @SWG\Items(type="string"),
   *   ),
   *   @SWG\Parameter(
   *     name = "receiver[]",
   *     description = "The name of the receiver.",
   *     in = "query",
   *     required = false,
   *     type = "array",
   *     collectionFormat = "multi",
   *     @SWG\Items(type="string"),
   *   ),
   *   @SWG\Parameter(
   *     name = "instrument[]",
   *     description = "The name of the instrument.",
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
    $getParams = $this->request->query->all();
    $years = [];
    if (isset($getParams['year']) && is_array($getParams['year'])) {
      foreach ($getParams['year'] as $year) {
        if (is_numeric($year)) {
          $years[$year] = 0;
        }
      }
    }

    // If years is empty we'll return an empty array as we can't run a valid
    // search.
    if (empty($years)) {
      return $this->jsonResponse([]);
    }

    // Check if we have a cached version. We'll only save cached versions for
    // the complete data of 1 or multiple years. If we've got a specific search
    // going on we'll never cache the data.
    ksort($years);

    $data = [
      'beleid' => $this->getBeleid($years, $getParams),
      'ontvangers' => $this->getOntvangers($years, $getParams),
      'instrumenten' => $this->getInstrumenten($years, $getParams),
    ];

    $cacheMetadata = [
      'contexts' => [
        'url.query_args',
      ],
      'tags' => ['minfin_import:financiele_instrumenten'],
    ];
    return $this->cacheableJsonResponse($data, $cacheMetadata);
  }

  /**
   * @SWG\Get(
   *   path = "/json/old/financiele_instrumenten/instruments",
   *   summary = "List of available instruments for the 'Wie ontvingen' chart",
   *   description = "List of available instruments for the 'Wie ontvingen' chart",
   *   operationId = "WieOntvingenInstruments",
   *   tags = { "Financiele instrumenten (old)" },
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   )
   * )
   */
  public function jsonInstruments(): JsonResponse {
    $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
    $query->distinct();
    $query->fields('fi', ['instrument']);
    $query->orderBy('fi.instrument');
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $data[$record['instrument']] = $record['instrument'];
    }

    return $this->jsonResponse($data);
  }

  /**
   * Get the beleid data.
   *
   * @param array $years
   *   The years.
   * @param array $filters
   *   The filters.
   *
   * @return array
   *   The data.
   */
  protected function getBeleid(array $years, array $filters): array {
    $values = [];
    foreach (array_keys($years) as $year) {
      $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
      $query->leftJoin('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND h.jaar = fi.jaar');
      $query->leftJoin('mf_artikel', 'a', 'a.artikel_minfin_id = fi.artikel_minfin_id AND a.hoofdstuk_minfin_id = fi.hoofdstuk_minfin_id AND a.jaar = fi.jaar');
      $query->addField('h', 'naam', 'hoofdstuk');
      $query->addField('fi', 'hoofdstuk_minfin_id', 'hoofdstuk_id');
      $query->addField('a', 'naam', 'artikel');
      $query->addField('fi', 'artikel_minfin_id', 'artikel_id');
      $query->fields('fi', ['regeling', 'jaar']);
      $query->condition('fi.jaar', $year, '=');
      $this->addQueryFilters($query, $filters);
      $query->addExpression('SUM(fi.bedrag)', 'bedrag');
      $query->groupBy('h.naam');
      $query->groupBy('fi.hoofdstuk_minfin_id');
      $query->groupBy('a.naam');
      $query->groupBy('fi.artikel_minfin_id');
      $query->groupBy('fi.regeling');
      $query->groupBy('fi.jaar');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $hoofdstuk = $record['hoofdstuk'] ?: 'hoofdstuk ' . $record['hoofdstuk_id'];
        $artikel = $record['artikel'] ?: 'artikel ' . $record['artikel_id'];

        $values[$hoofdstuk][$artikel][$record['regeling']][$record['jaar']] = (float) $record['bedrag'];
      }
    }

    $totaal = $years;
    $data1 = [];
    foreach ($values as $begrotingsHoofdstuk => $artikelen) {
      $totaal1 = $years;
      $data2 = [];
      foreach ($artikelen as $artikelNaam => $regelingen) {

        $totaal2 = $years;
        $data3 = [];
        foreach ($regelingen as $regelingNaam => $jaren) {

          $totaal3 = $years;
          foreach ($jaren as $jaar => $bedrag) {
            $totaal[$jaar] += $bedrag;
            $totaal1[$jaar] += $bedrag;
            $totaal2[$jaar] += $bedrag;
            $totaal3[$jaar] = $bedrag;
          }
          $data3[] = [
            'naam' => $regelingNaam,
            'realisatie' => $totaal3,
          ];
        }

        $data2[] = [
          'naam' => $artikelNaam,
          'realisatie' => $totaal2,
          'regelingen' => $data3,
        ];
      }

      $data1[] = [
        'naam' => $begrotingsHoofdstuk,
        'realisatie' => $totaal1,
        'artikelen' => $data2,
      ];
    }

    return [
      'naam' => 'Totaal',
      'realisatie' => $totaal,
      'hoofdstukken' => $data1,
    ];
  }

  /**
   * Get the ontvangers data.
   *
   * @param array $years
   *   The years.
   * @param array $filters
   *   The filters.
   *
   * @return array
   *   The data.
   */
  protected function getOntvangers(array $years, array $filters): array {
    $values = [];
    foreach (array_keys($years) as $year) {
      $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
      $query->fields('fi', ['ontvanger', 'jaar']);
      $query->condition('fi.jaar', $year, '=');
      $this->addQueryFilters($query, $filters);
      $query->addExpression('SUM(fi.bedrag)', 'bedrag');
      $query->groupBy('fi.ontvanger');
      $query->groupBy('fi.jaar');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $values[$record['ontvanger']][$record['jaar']] = floatval($record['bedrag']);
      }
    }

    $data = [];
    foreach ($values as $ontvanger => $jaren) {
      $data[] = [
        'naam' => $ontvanger,
        'realisatie' => array_replace($years, $jaren),
      ];
    }

    return $data;
  }

  /**
   * Get the instrumenten data.
   *
   * @param array $years
   *   The years.
   * @param array $filters
   *   The filters.
   *
   * @return array
   *   The data.
   */
  protected function getInstrumenten(array $years, array $filters): array {
    $values = [];
    foreach (array_keys($years) as $year) {
      $query = $this->connection->select('mf_financiele_instrumenten', 'fi');
      $query->fields('fi', ['instrument', 'jaar']);
      $query->condition('fi.jaar', $year, '=');
      $this->addQueryFilters($query, $filters);
      $query->addExpression('SUM(fi.bedrag)', 'bedrag');
      $query->groupBy('fi.instrument');
      $query->groupBy('fi.jaar');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $values[$record['instrument']][$record['jaar']] = floatval($record['bedrag']);
      }
    }

    $data = [];
    foreach ($values as $instrument => $jaren) {
      $data[] = [
        'naam' => $instrument,
        'realisatie' => array_replace($years, $jaren),
      ];
    }

    return $data;
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
      $query->condition('fi.instrument', $filters['instrument'], 'IN');
    }
    if (!empty($filters['receiver'])) {
      $query->condition('fi.ontvanger', $filters['receiver'], 'IN');
    }
    if (!empty($filters['regulation'])) {
      $query->condition('fi.regeling', $filters['regulation'], 'IN');
    }
  }

}
