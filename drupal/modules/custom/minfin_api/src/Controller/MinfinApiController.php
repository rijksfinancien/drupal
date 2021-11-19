<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

/**
 * The swagger API for minfin.
 */
class MinfinApiController extends BaseApiController {

  /**
   * @SWG\Get(
   *   path = "/json/minfin/get_chapters/{year}",
   *   summary = "Get a list of available chapters for the given year.",
   *   description = "Get a list of available chapters for the given year.",
   *   operationId = "mf_get_chapters",
   *   tags = { "Minfin API" },
   * @SWG\Parameter(
   *     name = "year",
   *     in = "path",
   *     required = true,
   *     type = "integer",
   *     description = "The calendar year.",
   *   ),
   * @SWG\Response(
   *     response = "200",
   *     description = "Successful call.<br />An array with the available chapters.",
   *   )
   * )
   */
  public function getChapters($year) {
    $data = [];
    $result = $this->connection->select('mf_hoofdstuk', 'h')
      ->fields('h', ['hoofdstuk_minfin_id', 'naam'])
      ->condition('h.jaar', $year, '=')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data[$record['hoofdstuk_minfin_id']] = $record['naam'];
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/minfin/available_phases",
   *   summary = "Get a list of available phases for each year.",
   *   description = "Get a list of all available phases and for which years the budget data is available.",
   *   operationId = "mf_available_phases",
   *   tags = { "Minfin API" },
   * @SWG\Response(
   *     response = "200",
   *     description = "Successful call.<br />An array with the available phases as keys and the available years as values.",
   *   )
   * )
   */
  public function getAvailablePhases() {
    return $this->jsonResponse($this->availablePhases());
  }

  /**
   * @SWG\Get(
   *   path = "/json/minfin/last_phase",
   *   summary = "Get the last added phase.",
   *   description = "Get a phase which was the last to release.",
   *   operationId = "mf_last_phase",
   *   tags = { "Minfin API" },
   * @SWG\Response(
   *     response = "200",
   *     description = "Successful call.<br />An array with the last added phase.",
   *   )
   * )
   */
  public function getLastPhase() {
    $phases = $this->availablePhases();
    $owb = (int) end($phases['owb']);
    $jv = (int) end($phases['jv']);

    if ($owb - $jv === 2) {
      return $this->jsonResponse(['phase' => 'owb', 'year' => $owb]);
    }
    return $this->jsonResponse(['phase' => 'jv', 'year' => $jv]);
  }

  /**
   * Get all available phases.
   *
   * @return array
   *   A list with all available phases.
   */
  private function availablePhases(): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->addExpression('DISTINCT(jaar)', 'jaar');
    $query->addExpression('SUM(bedrag_begroting)', 'begroting');
    $query->addExpression('SUM(bedrag_suppletoire1)', 'suppletoire1');
    $query->addExpression('SUM(bedrag_suppletoire2)', 'suppletoire2');
    $query->addExpression('SUM(bedrag_jaarverslag)', 'jaarverslag');
    $query->condition('bt.show', 1, '=');
    $query->groupBy('jaar');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      if (!empty($record['begroting'])) {
        $data['owb'][] = $record['jaar'];
      }
      if (!empty($record['suppletoire1'])) {
        $data['o1'][] = $record['jaar'];
      }
      if (!empty($record['suppletoire2'])) {
        $data['o2'][] = $record['jaar'];
      }
      if (!empty($record['jaarverslag'])) {
        $data['jv'][] = $record['jaar'];
      }
    }

    return $data;
  }

}
