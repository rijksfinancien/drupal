<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

/**
 * The swagger API for the Fiscale regelingen.
 */
class FiscaleRegelingenApiController extends BaseApiController {

  /**
   * @SWG\Get(
   *   path = "/json/fiscale_regelingen/{year}",
   *   summary = "Json data for the 'Fiscale regelingen' chart",
   *   description = "Json data for the 'Fiscale regelingen' chart",
   *   operationId = "fiscale_regelingen",
   *   tags = { "Fiscale regelingen" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function json($jaar) {
    $data = [];

    $query = $this->connection->select('mf_fiscale_regeling', 'fr');
    $query->leftJoin('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = fr.hoofdstuk_minfin_id AND h.jaar = fr.jaar');
    $query->leftJoin('mf_artikel', 'a', 'a.artikel_minfin_id = fr.artikel_minfin_id AND a.hoofdstuk_minfin_id = fr.hoofdstuk_minfin_id AND a.jaar = fr.jaar');
    $query->fields('fr', [
      'hoofdstuk_minfin_id',
      'artikel_minfin_id',
      'bedrag_begroting',
      'bedrag_premie',
      'bedrag_fiscaal',
    ]);
    $query->addField('h', 'naam', 'naam_hoofdstuk');
    $query->addField('a', 'naam', 'naam_artikel');
    $query->condition('fr.jaar', $jaar, '=');
    $query->where('!((fr.bedrag_begroting IS NULL) AND (fr.bedrag_premie IS NULL) AND (fr.bedrag_fiscaal IS NULL))');
    $result = $query->execute();

    $array = [];
    while ($record = $result->fetchAssoc()) {
      $child = [
        'label' => $record['naam_artikel'],
        'parts' => [
          'begroting' => (int) $record['bedrag_begroting'],
          'premie' => (int) $record['bedrag_premie'],
          'fiscaal' => (int) $record['bedrag_fiscaal'],
        ],
      ];

      if (isset($array[$record['hoofdstuk_minfin_id']])) {
        $array[$record['hoofdstuk_minfin_id']]['label'] = $record['naam_hoofdstuk'];
        $array[$record['hoofdstuk_minfin_id']]['parts']['begroting'] += (int) $record['bedrag_begroting'];
        $array[$record['hoofdstuk_minfin_id']]['parts']['premie'] += (int) $record['bedrag_premie'];
        $array[$record['hoofdstuk_minfin_id']]['parts']['fiscaal'] += (int) $record['bedrag_fiscaal'];
        $array[$record['hoofdstuk_minfin_id']]['children'][$record['artikel_minfin_id']] = $child;
      }
      else {
        $array[$record['hoofdstuk_minfin_id']] = [
          'label' => $record['naam_hoofdstuk'],
          'classname' => $record['naam_hoofdstuk'],
          'parts' => [
            'begroting' => (int) $record['bedrag_begroting'],
            'premie' => (int) $record['bedrag_premie'],
            'fiscaal' => (int) $record['bedrag_fiscaal'],
          ],
          'children' => [$record['artikel_minfin_id'] => $child],
        ];
      }
    }

    foreach ($array as $key => $value) {
      $value['key'] = $key;
      if (!empty($value['children'])) {
        $newchildren = [];
        foreach ($value['children'] as $childkey => $childvalue) {
          $childvalue['key'] = $childkey;
          $newchildren[] = $childvalue;
        }
        $value['children'] = $newchildren;
      }
      $data[] = $value;
    }

    return $this->jsonResponse($data);
  }

}
