<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The swagger API for the Verzelfstandigingen.
 */
class VerzelfstandigingenApiController extends BaseApiController {

  /**
   * @SWG\Get(
   *   path = "/json/verzelfstandigingen",
   *   summary = "Json data for the 'Verzelfstandigingen' chart",
   *   description = "Json data for the 'Verzelfstandigingen' chart",
   *   operationId = "Verzelfstandigingen",
   *   tags = { "Verzelfstandigingen" },
   *   @SWG\Parameter(
   *     name = "ministerie",
   *     description = "The name of the ministerie.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "type",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"agentschap", "zbo"},
   *   ),
   *   @SWG\Parameter(
   *     name = "jaar",
   *     description = "The calendar year.",
   *     in = "query",
   *     required = false,
   *     type = "integer",
   *   ),
   *   @SWG\Parameter(
   *     name = "soort",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *     enum={"omzet", "fte"},
   *   ),
   *   @SWG\Parameter(
   *     name = "min",
   *     description = "The min value.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Parameter(
   *     name = "max",
   *     description = "The max value.",
   *     in = "query",
   *     required = false,
   *     type = "string",
   *   ),
   *   @SWG\Response(
   *     response = "200",
   *     description = "Successful call",
   *   )
   * )
   */
  public function json(): JsonResponse {
    $availableYears = $this->getYears();
    $getParams = $this->request->query->all();
    $activeYear = (int) ($getParams['jaar'] ?? max($availableYears));

    // Make sure this is a valid db field.
    $field = 'fte';
    if (isset($getParams['soort']) && ($soort = $getParams['soort']) && in_array($soort, ['fte', 'omzet'])) {
      $field = $soort === 'omzet' ? 'bedrag' : $soort;
    }

    $information = $this->getInformation();

    $data = [
      'ministerie' => $this->getMinisteries(),
      'type' => ['agentschap', 'zbo'],
      'jaar' => $availableYears,
      'soort' => ['omzet', 'fte'],
      'min' => $this->getValue($field, 'MIN'),
      'max' => $this->getValue($field, 'MAX'),
      'result' => [],
    ];

    $years = [$activeYear => NULL];
    if (isset($availableYears[$activeYear + 1])) {
      $years[$activeYear + 1] = NULL;
    }
    if (isset($availableYears[$activeYear + 2])) {
      $years[$activeYear + 2] = NULL;
    }
    if (isset($availableYears[$activeYear - 1])) {
      $years[$activeYear - 1] = NULL;
    }
    if (isset($availableYears[$activeYear - 2])) {
      $years[$activeYear - 2] = NULL;
    }
    ksort($years);

    foreach (array_keys($years) as $year) {
      $query = $this->connection->select('mf_verzelfstandiging', 'v');
      $query->fields('v', ['verzelfstandiging_id', 'jaar', 'ministerie', 'organisatie', 'type', 'fte', 'bedrag']);
      $query->condition('v.jaar', $year, '=');
      if (isset($getParams['ministerie'])) {
        $query->condition('v.ministerie', $getParams['ministerie'], '=');
      }
      if (isset($getParams['type'])) {
        $query->condition('v.type', $getParams['type'], '=');
      }
      if (isset($getParams['min'])) {
        $query->condition('v.' . $field, $getParams['min'], '>=');
      }
      if (isset($getParams['max'])) {
        $query->condition('v.' . $field, $getParams['max'], '<=');
      }
      $query->orderBy('v.' . $field, 'DESC');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $key = $record['type'] . '|' . $record['organisatie'];

        if (!isset($data['result'][$key])) {
          $data['result'][$key] = [
            'id' => $record['verzelfstandiging_id'],
            'titel' => $record['organisatie'],
            'ministerie' => $record['ministerie'],
            'type' => $record['type'],
            'jaar' => $activeYear,
            'omzet' => $years,
            'fte' => $years,
            'info' => $information[$record['type']][$record['organisatie']] ?? [],
          ];
        }
        $data['result'][$key]['omzet'][$year] = (int) $record['bedrag'];
        $data['result'][$key]['fte'][$year] = round($record['fte'], 2);
      }
    }

    return $this->jsonResponse($data);
  }

  /**
   * Returns an array with detailed information for the organisations.
   *
   * @return array
   *   An array with detailed information for the organisations.
   */
  private function getInformation(): array {
    $data = [];
    $result = $this->connection->select('mf_verzelfstandiging_uitleg', 'vu')
      ->fields('vu', [
        'type',
        'naam',
        'afkorting',
        'ministerie',
        'website',
        'resource_identifier',
        'fte',
        'beschrijving',
        'taken_en_bevoegdheden',
        'evaluaties',
        'rapport',
        'rapport_titel',
      ])
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $info = [
        'type' => $record['type'],
      ];
      if ($record['type'] === 'agentschap') {
        $info['beschrijving'] = $record['beschrijving'] ?: NULL;
      }
      elseif ($record['type'] === 'zbo') {
        $info['beschrijving'] = $record['taken_en_bevoegdheden'] ?: (NULL);
      }
      $info['website'] = $record['website'] ?: NULL;

      $data[$record['type']][$record['naam']] = $info;
      // In the original data most names include the shortcut in brackets.
      $secondName = $record['naam'] . ' (' . $record['afkorting'] . ')';
      if (!isset($data[$record['type']][$secondName])) {
        $data[$record['type']][$secondName] = $info;
      }
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
    $years = [];
    $query = $this->connection->select('mf_verzelfstandiging', 'v');
    $query->distinct();
    $query->fields('v', ['jaar']);
    $query->orderBy('v.jaar', 'ASC');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $year = (int) $record['jaar'];
      $years[$year] = $year;
    }
    return $years;
  }

  /**
   * Get the available ministeries.
   *
   * @return array
   *   The ministeries.
   */
  private function getMinisteries(): array {
    $query = $this->connection->select('mf_verzelfstandiging', 'v');
    $query->distinct();
    $query->fields('v', ['ministerie']);
    $query->orderBy('v.ministerie', 'ASC');
    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Get a specific value.
   *
   * @param string $field
   *   The field to get the value from.
   * @param string $type
   *   The type.
   *
   * @return float
   *   The value.
   */
  private function getValue(string $field, string $type): float {
    if (!in_array($field, ['fte', 'bedrag'])) {
      return 0;
    }

    if (!in_array($type, ['MIN', 'MAX'])) {
      return 0;
    }

    $query = $this->connection->select('mf_verzelfstandiging', 'v');
    // Its secure because of the checks above.
    $query->addExpression($type . '(v.' . $field . ')', 'value');
    return (float) $query->execute()->fetchField();
  }

}
