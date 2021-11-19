<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api_public\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * The swagger API for budgettaire tabellen.
 *
 * @SWG\Parameter(
 *   parameter = "btVuo",
 *   name = "vuo",
 *   in = "query",
 *   type = "string",
 *   enum={"U", "O", "V"},
 *   description = "Use 'U' to get the data for the 'expenditures'.<br />Use 'O' to get the data for the 'revenues'.",
 * ),
 * @SWG\Parameter(
 *   parameter = "btChapter",
 *   name = "chapter",
 *   in = "query",
 *   type = "string",
 *   description = "The chapter id.",
 * ),
 * @SWG\Parameter(
 *   parameter = "btChapterName",
 *   name = "chapter_name",
 *   in = "query",
 *   type ="string",
 *   description = "Partial search on the chapter name.",
 * ),
 * @SWG\Parameter(
 *   parameter = "btArticle",
 *   name = "article",
 *   in = "query",
 *   type = "string",
 *   description = "The article id.",
 * ),
 * @SWG\Parameter(
 *   parameter = "btArticleName",
 *   name = "article_name",
 *   in = "query",
 *   type ="string",
 *   description = "Partial search on the article name.",
 * ),
 * @SWG\Parameter(
 *   parameter = "btSubArticle",
 *   name = "sub_article",
 *   in = "query",
 *   type ="string",
 *   description = "The sub-article id.",
 * ),
 * @SWG\Parameter(
 *   parameter = "btMinister",
 *   name = "minister",
 *   in = "query",
 *   type ="string",
 *   description = "Partial search on the name of the minister.",
 * ),
 */
class BudgettaireTabellenApiController extends BasePublicApiController {

  /**
   * Get the data for the budgettaire tabellen.
   *
   * @return array
   *   The requested data.
   */
  public function getData(): array {
    $data = [];

    $phases = ['OWB', 'JV'];
    if (($phase = strtoupper($this->request->query->get('phase'))) && in_array($phase, $phases, TRUE)) {
      $phases = [$phase];
    }

    foreach ($phases as $phase) {
      $query = $this->connection->select('mf_b_tabel', 'bt');
      $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id AND h.jaar = bt.jaar');
      $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id AND a.jaar = bt.jaar');
      $query->leftJoin('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
      $query->leftJoin('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
      $query->leftJoin('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
      $query->leftJoin('mf_hoofdstuk_heeft_minister', 'hhm', 'hhm.hoofdstuk_id = bt.hoofdstuk_id AND hhm.fase = :fase', [':fase' => $phase]);
      $query->leftJoin('mf_minister', 'm', 'm.minister_id = hhm.minister_id');

      $query->addField('h', 'hoofdstuk_minfin_id', 'hoofdstuk_nummer');
      $query->addField('h', 'naam', 'hoofdstuk_naam');
      $query->addField('a', 'artikel_minfin_id', 'artikel_nummer');
      $query->addField('a', 'naam', 'artikel_naam');
      $query->addField('ao', 'artikelonderdeel_minfin_id', 'artikelonderdeel_nummer');
      $query->addField('ao', 'naam', 'artikelonderdeel_naam');
      $query->addField('iua', 'naam', 'instrument_of_uitsplitsing_apparaat_naam');
      $query->addField('iua', 'instrument_of_uitsplitsing_apparaat_minfin_id', 'instrument_of_uitsplitsing_apparaat_nummer');
      $query->addField('rd', 'naam', 'regeling_detailniveau_naam');
      $query->addField('rd', 'regeling_detailniveau_minfin_id', 'regeling_detailniveau_nummer');
      $query->addField('m', 'naam', 'verantwoordelijk_minister');
      $query->addField('bt', 'jaar', 'jaar');
      $query->addField('bt', 'vuo', 'vuo');
      if (strtoupper($phase) === 'JV') {
        $query->addField('bt', 'bedrag_jaarverslag', 'bedrag');
      }
      elseif (strtoupper($phase) === 'O1') {
        $query->addField('bt', 'bedrag_suppletoire1', 'bedrag');
      }
      elseif (strtoupper($phase) === 'O2') {
        $query->addField('bt', 'bedrag_suppletoire2', 'bedrag');
      }
      else {
        $query->addField('bt', 'bedrag_begroting', 'bedrag');
      }

      if ($year = $this->request->query->get('year')) {
        $query->condition('bt.jaar', $year, '=');
      }
      if ($vuo = $this->request->query->get('vuo')) {
        $query->condition('bt.vuo', $vuo, '=');
      }
      if ($hoofdstukMinfinId = $this->request->query->get('chapter')) {
        $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      }
      if ($chapterName = $this->request->query->get('chapter_name')) {
        $query->condition('h.naam', '%' . $this->connection->escapeLike($chapterName) . '%', 'LIKE');
      }
      if ($artikelMinfinId = $this->request->query->get('article')) {
        $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      }
      if ($articleName = $this->request->query->get('article_name')) {
        $query->condition('a.naam', '%' . $this->connection->escapeLike($articleName) . '%', 'LIKE');
      }
      if ($artikelonderdeelMinfinId = $this->request->query->get('sub_article')) {
        $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
      }
      if ($ministerName = $this->request->query->get('minister')) {
        $query->condition('m.naam', '%' . $this->connection->escapeLike($ministerName) . '%', 'LIKE');
      }
      $query->condition('bt.show', 1, '=');

      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $index = implode('.', [
          $record['hoofdstuk_nummer'],
          $record['artikel_nummer'],
          $record['artikelonderdeel_nummer'] ?? 0,
          $record['instrument_of_uitsplitsing_apparaat_nummer'] ?? 0,
          $record['regeling_detailniveau_nummer'] ?? 0,
          $record['vuo'],
        ]);

        $data[] = [
          'index' => $index,
          'jaar' => (int) $record['jaar'],
          'fase' => $phase,
          'verantwoordelijk_minister' => $record['verantwoordelijk_minister'],
          'hoofdstuk_naam' => $record['hoofdstuk_naam'],
          'hoofdstuk_nummer' => $record['hoofdstuk_nummer'],
          'artikel_naam' => $record['artikel_naam'],
          'artikel_nummer' => $record['artikel_nummer'],
          'beleid_of_niet_beleid' => '',
          'rbv_model' => '',
          'artikelonderdeel_naam' => $record['artikelonderdeel_naam'] ?? '',
          'artikelonderdeel_nummer' => $record['artikelonderdeel_nummer'] ?? '',
          'instrument_of_uitsplitsing_apparaat_naam' => $record['instrument_of_uitsplitsing_apparaat_naam'] ?? '',
          'instrument_of_uitsplitsing_apparaat_nummer' => $record['instrument_of_uitsplitsing_apparaat_nummer'] ?? '',
          'regeling_detailniveau_naam' => $record['regeling_detailniveau_naam'] ?? '',
          'regeling_detailniveau_nummer' => $record['regeling_detailniveau_nummer'] ?? '',
          'vuo' => $record['vuo'],
          'bedrag' => (int) $record['bedrag'],
        ];
        $record['bedrag'] = (int) $record['bedrag'];
      }
    }

    return $data;
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/json/budgettaire_tabellen",
   *   summary = "Query the budgettaire tabellen data.",
   *   description = "Query the budgettaire tabellen data.",
   *   operationId = "bt_query_data_json",
   *   tags = { "Budgettaire Tabellen" },
   *   @SWG\Parameter(ref="#/parameters/yearQuery"),
   *   @SWG\Parameter(ref="#/parameters/phaseQuery"),
   *   @SWG\Parameter(ref="#/parameters/btVuo"),
   *   @SWG\Parameter(ref="#/parameters/btChapter"),
   *   @SWG\Parameter(ref="#/parameters/btChapterName"),
   *   @SWG\Parameter(ref="#/parameters/btArticle"),
   *   @SWG\Parameter(ref="#/parameters/btArticleName"),
   *   @SWG\Parameter(ref="#/parameters/btSubArticle"),
   *   @SWG\Parameter(ref="#/parameters/btMinister"),
   *   @SWG\Response(response=200, ref="#/responses/success"),
   *   @SWG\Response(response=404, ref="#/responses/failure"),
   * )
   */
  public function json(): JsonResponse {
    $data = $this->getData();
    return $this->jsonResponse($data, !empty($data) ? 200 : 404);
  }

  /**
   * @SWG\Get(
   *   path = "/open-data/api/csv/budgettaire_tabellen",
   *   summary = "Query the budgettaire tabellen data.",
   *   description = "Query the budgettaire tabellen data.",
   *   operationId = "bt_query_data_csv",
   *   tags = { "Budgettaire Tabellen" },
   *   @SWG\Parameter(ref="#/parameters/yearQuery"),
   *   @SWG\Parameter(ref="#/parameters/phaseQuery"),
   *   @SWG\Parameter(ref="#/parameters/btVuo"),
   *   @SWG\Parameter(ref="#/parameters/btChapter"),
   *   @SWG\Parameter(ref="#/parameters/btChapterName"),
   *   @SWG\Parameter(ref="#/parameters/btArticle"),
   *   @SWG\Parameter(ref="#/parameters/btArticleName"),
   *   @SWG\Parameter(ref="#/parameters/btSubArticle"),
   *   @SWG\Parameter(ref="#/parameters/btMinister"),
   *   @SWG\Response(response=200, ref="#/responses/success"),
   *   @SWG\Response(response=404, ref="#/responses/failure"),
   * )
   */
  public function csv(): Response {
    $data = $this->getData();
    return $this->csvResponse('budgettaire_tabellen', $data);
  }

}
