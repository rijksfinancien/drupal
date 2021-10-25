<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

/**
 * The swagger API for the Begrotingsstaten.
 */
class BegrotingsstatenApiController extends BaseApiController {

  /**
   * @SWG\Get(
   *   path = "/json/begrotingsstaten/{year}/{vuo}",
   *   summary = "Get the begrotingsstaten data.",
   *   description = "Get all chapters.",
   *   operationId = "bs_get_data",
   *   tags = { "Begrotingsstaten" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   *
   * @SWG\Get(
   *   path = "/json/begrotingsstaten/{year}/{vuo}/{chapter}",
   *   summary = "Get the begrotingsstaten data.",
   *   description = "Get article data.",
   *   operationId = "bs_get_data_2",
   *   tags = { "Begrotingsstaten" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function json($jaar, $vuo, $hoofdstukMinfinId = NULL) {
    $data = [];

    $query = $this->connection->select('mf_begrotingsstaat', 'bs');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bs.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bs.artikel_id');
    if ($hoofdstukMinfinId) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->addField('a', 'artikel_minfin_id', 'identifier');
      $query->addField('a', 'naam', 'title');
      $query->groupBy('a.artikel_minfin_id');
      $query->groupBy('a.naam');
    }
    else {
      $query->addField('h', 'hoofdstuk_minfin_id', 'identifier');
      $query->addField('h', 'naam', 'title');
      $query->groupBy('h.naam');
    }
    $query->condition('bs.jaar', $jaar, '=');
    $query->condition('bs.vuo', $vuo, '=');
    $query->addExpression('SUM(bs.bedrag_begroting)', 'begroting');
    $query->addExpression('SUM(bs.bedrag_suppletoire1)', 'suppletoire1');
    $query->addExpression('SUM(bs.bedrag_suppletoire2)', 'suppletoire2');
    $query->addExpression('SUM(bs.bedrag_jaarverslag)', 'jaarverslag');
    $query->groupBy('bs.jaar');
    $query->groupBy('bs.vuo');
    $query->groupBy('h.hoofdstuk_minfin_id');

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['begroting'] = (int) $record['begroting'];
      $record['suppletoire1'] = (int) $record['suppletoire1'];
      $record['suppletoire2'] = (int) $record['suppletoire2'];
      $record['jaarverslag'] = (int) $record['jaarverslag'];
      $data[] = $record;
    }

    return $this->jsonResponse($data);
  }

}
