<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Url;

/**
 * The swagger API for the begrotings visual.
 */
class BegrotingsVisualApiController extends BaseApiController {

  /**
   * @SWG\Parameter(
   *   parameter = "bvYear",
   *   name = "year",
   *   in = "path",
   *   required = true,
   *   type = "integer",
   *   description = "The calendar year.",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "bvPhase",
   *   name = "phase",
   *   in = "path",
   *   required = true,
   *   type = "string",
   *   enum={"OWB", "O1", "O2", "JV"},
   *   description = "The phase you want to receive data for.<br />Use 'OWB' to get the data for the the 'Annual budget'.<br />Use 'JV' to get the data for the 'year report'.",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "bvVuo",
   *   name = "vuo",
   *   in = "path",
   *   required = true,
   *   type = "string",
   *   enum={"U", "O", "V"},
   *   description = "Use 'U' to get the data for the 'expenditures'.<br />Use 'O' to get the data for the 'revenues'.",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "bvChapter",
   *   name = "chapter",
   *   in = "path",
   *   required = true,
   *   type = "string",
   *   description = "The chapter id.<br />The required identifier can be found in the previous call in the field 'identifier'.",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "bvArticle",
   *   name = "article",
   *   in = "path",
   *   required = true,
   *   type = "string",
   *   description = "The article id.<br />The required identifier can be found in the previous call in the field 'identifier'.",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "bvSub1",
   *   name = "sub1",
   *   in = "path",
   *   required = true,
   *   type = "string",
   *   description = "The identifier of the subdivision.<br />The required identifier can be found in the previous call in the field 'identifier'.",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "bvSub2",
   *   name = "sub2",
   *   in = "path",
   *   required = true,
   *   type = "string",
   *   description = "The identifier of the second subdivision.<br />The required identifier can be found in the previous call in the field 'identifier'.",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "bvSub3",
   *   name = "sub3",
   *   in = "path",
   *   required = true,
   *   type = "string",
   *   description = "The identifier of the third subdivision.<br />The required identifier can be found in the previous call in the field 'identifier'.",
   * );
   *
   * @SWG\Response(
   *   response = "bvSuccess",
   *   description = "Successful call.<br />The returned json array will contain some base information with all the requested values inside the children element.",
   * );
   *
   * @SWG\Response(
   *   response = "bvFailure",
   *   description = "No data found for the requested parameters.",
   * )
   */
  public function json($jaar, $fase, $vuo, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL, $sub1 = NULL, $sub2 = NULL, $sub3 = NULL, $triple = FALSE, $depth = 2) {
    // Check if we have any data for the given year/phase.
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('jaar', $jaar, '=');
    $query->condition('bt.show', 1, '=');
    $query->groupBy('jaar');
    if (!$query->execute()->fetchField()) {
      return $this->jsonResponse([], 404);
    }

    $years = [$jaar];
    if ($triple) {
      $years = $this->getYearsToShow($jaar, $fase);
    }

    $percentage = NULL;
    if (!empty($hoofdstukMinfinId)) {
      $now = $this->getTotalSum($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2, $sub3);
      $previous = $this->getTotalSum(($jaar - 1), $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2, $sub3);
      if (!empty($previous) && !empty($now)) {
        $percentage = round(($now / $previous) * 100, 2);
      }
    }

    $description = '';
    if (!empty($artikelMinfinId)) {
      $query = $this->connection->select('mf_artikel', 'a');
      $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = a.hoofdstuk_minfin_id AND h.jaar = a.jaar');
      $query->fields('a', ['omschrijving']);
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.jaar', $jaar, '=');
      $description = (string) $query->execute()->fetchField();
    }

    $kamerstuk = NULL;
    if ($hoofdstukMinfinId && !isset($sub1)) {
      // Build the kamerstuk URL.
      $type = 'memorie_van_toelichting';
      try {
        if ($fase === 'JV') {
          $type = 'jaarverslag';
          $routeName = 'minfin.jaarverslag.table_of_contents';
          $params = [
            'year' => $jaar,
            'hoofdstukMinfinId' => $hoofdstukMinfinId,
          ];
          if ($artikelMinfinId) {
            $routeName = 'minfin.jaarverslag.anchor';
          }
        }
        else {
          $kamerstukPhases = [
            'O1' => '1SUPP',
            'O2' => '2SUPP',
          ];
          $routeName = 'minfin.memorie_van_toelichting.table_of_contents';
          $params = [
            'year' => $jaar,
            'phase' => $kamerstukPhases[$fase] ?: $fase,
            'hoofdstukMinfinId' => $hoofdstukMinfinId,
          ];
          if ($artikelMinfinId) {
            $routeName = 'minfin.memorie_van_toelichting.anchor';
          }
        }

        if ($artikelMinfinId) {
          $query = $this->connection->select('mf_kamerstuk', 'k');
          $query->fields('k', ['anchor']);
          $query->condition('k.type', $type, '=');
          $query->condition('k.fase', $fase, '=');
          $query->condition('k.jaar', $jaar, '=');
          $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
          $query->condition('k.artikel_minfin_id', $artikelMinfinId, '=');
          $params['anchor'] = $query->execute()->fetchField();
        }

        $url = Url::fromRoute($routeName, $params);
        if ($url->access()) {
          $kamerstuk = $url->toString();
        }
      }
      catch (\Exception $e) {
        // Do nothing.
      }
    }

    $data = [];
    foreach ($years as $yearToShow) {
      $data[$yearToShow] = [
        'title' => $this->getName($yearToShow, $fase, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2, $sub3, FALSE),
        'back_title' => $this->getName($yearToShow, $fase, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2, $sub3, TRUE),
        'description' => $description,
        'links' => $this->getArtikelLinks($hoofdstukMinfinId, $artikelMinfinId, $sub1),
        'kamerstuk' => $kamerstuk,
        'percentage' => $percentage,
        'children' => [],
      ];

      if (isset($sub2)) {
        $data[$yearToShow]['children'] = $this->getRegelingDetailniveau($yearToShow, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2);
      }
      elseif (isset($sub1)) {
        $data[$yearToShow]['children'] = $this->getInstrumentOfUitsplitsingApparaat($yearToShow, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, ($depth - 1));
      }
      elseif ($artikelMinfinId) {
        $data[$yearToShow]['children'] = $this->getArtikelonderdeelData($yearToShow, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, ($depth - 1));
      }
      elseif ($hoofdstukMinfinId) {
        $data[$yearToShow]['children'] = $this->getArtikelData($yearToShow, $fase, $vuo, $hoofdstukMinfinId, ($depth - 1));
      }
      else {
        $data[$yearToShow]['children'] = $this->getHoofdstukData($yearToShow, $fase, $vuo, ($depth - 1));
      }
    }

    return $this->jsonResponse($data, !empty($data[$jaar]['children']) ? 200 : 404);
  }

  /**
   * @SWG\Response(
   *   response = "csvDownload",
   *   description = "A csv file with the requested data.",
   * );
   *
   * @SWG\Get(
   *   path = "/csv/budgettaire_tabellen/{year}/{phase}/{vuo}",
   *   summary = "Get all chapters.",
   *   description = "Get all chapters for the given parameters.",
   *   operationId = "bv_get_csv_chapters",
   *   tags = { "Butget Visualisation (CSV)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Response(response=200, ref="#/responses/csvDownload")
   * )
   *
   * @SWG\Get(
   *   path = "/csv/budgettaire_tabellen/{year}/{phase}/{vuo}/{chapter}",
   *   summary = "Get all articles.",
   *   description = "Get all articles for the given parameters.",
   *   operationId = "bv_get_csv_articles",
   *   tags = { "Butget Visualisation (CSV)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Response(response=200, ref="#/responses/csvDownload")
   * )
   *
   * @SWG\Get(
   *   path = "/csv/budgettaire_tabellen/{year}/{phase}/{vuo}/{chapter}/{article}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_csv_data1",
   *   tags = { "Butget Visualisation (CSV)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Response(response=200, ref="#/responses/csvDownload")
   * )
   *
   * @SWG\Get(
   *   path = "/csv/budgettaire_tabellen/{year}/{phase}/{vuo}/{chapter}/{article}/{sub1}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_csv_data2",
   *   tags = { "Butget Visualisation (CSV)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Parameter(ref="#/parameters/bvSub1"),
   *   @SWG\Response(response=200, ref="#/responses/csvDownload")
   * )
   *
   * @SWG\Get(
   *   path = "/csv/budgettaire_tabellen/{year}/{phase}/{vuo}/{chapter}/{article}/{sub1}/{sub2}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_csv_data3",
   *   tags = { "Butget Visualisation (CSV)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Parameter(ref="#/parameters/bvSub1"),
   *   @SWG\Parameter(ref="#/parameters/bvSub2"),
   *   @SWG\Response(response=200, ref="#/responses/csvDownload")
   * )
   */
  public function csv($jaar, $fase, $vuo, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL, $sub1 = NULL, $sub2 = NULL) {
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->leftJoin('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->leftJoin('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->leftJoin('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
    $query->condition('bt.jaar', $jaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('bt.show', 1, '=');
    $query->addField('bt', 'jaar', 'jaar');
    $query->addField('h', 'hoofdstuk_minfin_id', 'begrotingshoofdstuk');
    $query->addField('h', 'naam', 'hoofdstuk_naam');
    $query->addField('a', 'artikel_minfin_id', 'artikelnummer');
    $query->addField('h', 'naam', 'artikel_naam');
    $query->groupBy('bt.jaar');
    $query->groupBy('h.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $query->groupBy('a.naam');
    $query->groupBy('a.artikel_minfin_id');
    $query->orderBy('h.hoofdstuk_minfin_id', 'ASC');
    $query->orderBy('a.artikel_minfin_id', 'ASC');

    if ($hoofdstukMinfinId) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->addField('ao', 'naam', 'artikelnummer_onderverdeling');
      $query->groupBy('ao.naam');
    }
    if ($artikelMinfinId) {
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->addField('iua', 'naam', 'instrument_of_uitsplitsing_apparaat');
      $query->groupBy('iua.naam');
    }
    if (isset($sub1)) {
      $query->condition('ao.artikelonderdeel_minfin_id', $sub1, '=');
      $query->addField('rd', 'naam', 'regeling_detailniveau');
      $query->groupBy('rd.naam');
    }
    if (isset($sub2)) {
      $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $sub2, '=');
    }

    $this->addAmmountToQuery($query, $fase);
    $result = $query->execute();

    $data = [];
    while ($record = $result->fetchAssoc()) {
      $values = [
        'Jaar' => $record['jaar'],
        'Begrotingshoofdstuk' => $record['begrotingshoofdstuk'],
        'Hoofdstuk naam' => $record['hoofdstuk_naam'],
        'Artikelnummer' => $record['artikelnummer'],
        'Artikel naam' => $record['artikel_naam'],
      ];
      if ($hoofdstukMinfinId) {
        $values['Artikelnummer onderverdeling'] = $record['artikelnummer_onderverdeling'];
      }
      if ($artikelMinfinId) {
        $values['Instrument of uitsplitsing apparaat'] = $record['instrument_of_uitsplitsing_apparaat'];
      }
      if ($sub1) {
        $values['Regeling detailniveau'] = $record['regeling_detailniveau'];
      }
      $values['Bedrag (x1.000)'] = (int) $record['amount'];
      $data[] = $values;
    }

    return $this->csvResponse('data', $data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/single/{year}/{phase}/{vuo}",
   *   summary = "Get all chapters.",
   *   description = "Get all chapters for the given parameters.",
   *   operationId = "bv_get_s_chapters",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   *
   * @SWG\Get(
   *   path = "/json/triple/{year}/{phase}/{vuo}",
   *   summary = "Get all chapters.",
   *   description = "Get all chapters for the given parameters.",
   *   operationId = "bv_get_t_chapters",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  protected function getHoofdstukData($jaar, $fase, $vuo, $depth): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->addField('h', 'naam', 'title');
    $query->addField('h', 'hoofdstuk_minfin_id', 'identifier');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('bt.jaar', $jaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->groupBy('h.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['amount'] = (int) $record['amount'];
      if ($depth > 0) {
        $record['children'] = $this->getArtikelData($jaar, $fase, $vuo, $record['identifier'], ($depth - 1));
      }
      $data[] = $record;
    }

    return $data;
  }

  /**
   * @SWG\Get(
   *   path = "/json/single/{year}/{phase}/{vuo}/{chapter}",
   *   summary = "Get all articles.",
   *   description = "Get all articles for the given parameters.",
   *   operationId = "bv_get_s_articles",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   *
   * @SWG\Get(
   *   path = "/json/triple/{year}/{phase}/{vuo}/{chapter}",
   *   summary = "Get all articles.",
   *   description = "Get all articles for the given parameters.",
   *   operationId = "bv_get_t_articles",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  protected function getArtikelData($jaar, $fase, $vuo, $hoofdstukMinfinId, $depth): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->addField('a', 'naam', 'title');
    $query->addField('a', 'artikel_minfin_id', 'identifier');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('bt.jaar', $jaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    $query->groupBy('a.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $query->groupBy('a.artikel_minfin_id');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['amount'] = (int) $record['amount'];
      if ($depth > 0) {
        $record['children'] = $this->getArtikelonderdeelData($jaar, $fase, $vuo, $hoofdstukMinfinId, $record['identifier'], ($depth - 1));
      }
      $data[] = $record;
    }

    return $data;
  }

  /**
   * @SWG\Get(
   *   path = "/json/single/{year}/{phase}/{vuo}/{chapter}/{article}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_s_data1",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   *
   * @SWG\Get(
   *   path = "/json/triple/{year}/{phase}/{vuo}/{chapter}/{article}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_t_data1",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  protected function getArtikelonderdeelData($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $depth): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->join('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->addField('ao', 'naam', 'title');
    $query->addField('ao', 'artikelonderdeel_minfin_id', 'identifier');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('bt.jaar', $jaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
    $query->groupBy('ao.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $query->groupBy('a.artikel_minfin_id');
    $query->groupBy('ao.artikelonderdeel_minfin_id');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['amount'] = (int) $record['amount'];
      if ($depth > 0) {
        $record['children'] = $this->getInstrumentOfUitsplitsingApparaat($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $record['identifier'], ($depth - 1));
      }
      $data[] = $record;
    }

    return $data;
  }

  /**
   * @SWG\Get(
   *   path = "/json/single/{year}/{phase}/{vuo}/{chapter}/{article}/{sub1}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_s_data2",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Parameter(ref="#/parameters/bvSub1"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   *
   * @SWG\Get(
   *   path = "/json/triple/{year}/{phase}/{vuo}/{chapter}/{article}/{sub1}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_t_data2",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Parameter(ref="#/parameters/bvSub1"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  protected function getInstrumentOfUitsplitsingApparaat($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $depth): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->join('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->join('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->addField('iua', 'naam', 'title');
    $query->addField('iua', 'instrument_of_uitsplitsing_apparaat_minfin_id', 'identifier');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('bt.jaar', $jaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
    $query->condition('ao.artikelonderdeel_minfin_id', $sub1);
    $query->groupBy('iua.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $query->groupBy('a.artikel_minfin_id');
    $query->groupBy('ao.artikelonderdeel_minfin_id');
    $query->groupBy('iua.instrument_of_uitsplitsing_apparaat_minfin_id');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['amount'] = (int) $record['amount'];
      if ($depth > 0) {
        $record['children'] = $this->getRegelingDetailniveau($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $record['identifier']);
      }
      $data[] = $record;
    }

    return $data;
  }

  /**
   * @SWG\Get(
   *   path = "/json/single/{year}/{phase}/{vuo}/{chapter}/{article}/{sub1}/{sub2}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_s_data3",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Parameter(ref="#/parameters/bvSub1"),
   *   @SWG\Parameter(ref="#/parameters/bvSub2"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   *
   * @SWG\Get(
   *   path = "/json/triple/{year}/{phase}/{vuo}/{chapter}/{article}/{sub1}/{sub2}",
   *   summary = "Get all data.",
   *   description = "Get all data for the given parameters.",
   *   operationId = "bv_get_t_data3",
   *   tags = { "Butget Visualisation (JSON)" },
   *   @SWG\Parameter(ref="#/parameters/bvYear"),
   *   @SWG\Parameter(ref="#/parameters/bvPhase"),
   *   @SWG\Parameter(ref="#/parameters/bvVuo"),
   *   @SWG\Parameter(ref="#/parameters/bvChapter"),
   *   @SWG\Parameter(ref="#/parameters/bvArticle"),
   *   @SWG\Parameter(ref="#/parameters/bvSub1"),
   *   @SWG\Parameter(ref="#/parameters/bvSub2"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  protected function getRegelingDetailniveau($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->join('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->join('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->join('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
    $query->addField('rd', 'naam', 'title');
    $query->addField('rd', 'regeling_detailniveau_minfin_id', 'identifier');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('bt.jaar', $jaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('bt.show', 1, '=');
    $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
    $query->condition('ao.artikelonderdeel_minfin_id', $sub1);
    $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $sub2);
    $query->groupBy('rd.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $query->groupBy('a.artikel_minfin_id');
    $query->groupBy('ao.artikelonderdeel_minfin_id');
    $query->groupBy('iua.instrument_of_uitsplitsing_apparaat_minfin_id');
    $query->groupBy('rd.regeling_detailniveau_minfin_id');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['amount'] = (int) $record['amount'];
      $data[] = $record;
    }

    return $data;
  }

  /**
   * @todo see if this needs to be a seprate call or a combined call with the normal output.
   */
  public function getLegenda($jaar, $fase, $vuo, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL, $sub1 = NULL, $sub2 = NULL, $triple = FALSE) {
    $data = [];
    $years = [$jaar];
    if ($triple) {
      $years = $this->getYearsToShow($jaar, $fase);
    }

    foreach ($years as $yearToShow) {
      if (isset($sub2)) {
        $data[$yearToShow]['children'] = $this->getRegelingDetailniveau($yearToShow, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2);
      }
      elseif (isset($sub1)) {
        $data[$yearToShow]['children'] = $this->getInstrumentOfUitsplitsingApparaat($yearToShow, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, 0);
      }
      elseif ($artikelMinfinId) {
        $data[$yearToShow]['children'] = $this->getArtikelonderdeelData($yearToShow, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, 0);
      }
      elseif ($hoofdstukMinfinId) {
        $data[$yearToShow]['children'] = $this->getArtikelData($yearToShow, $fase, $vuo, $hoofdstukMinfinId, 0);
      }
      else {
        $data[$yearToShow]['children'] = $this->getHoofdstukData($yearToShow, $fase, $vuo, 0);
      }
    }

    $legenda = [];
    if (isset($data[$jaar])) {
      foreach ($data[$jaar]['children'] as $v) {
        $legenda[$v['identifier']] = $v;
      }
      unset($data[$jaar]);
    }

    foreach ($data as $values) {
      foreach ($values['children'] as $v) {
        if (!isset($legenda[$v['identifier']])) {
          $legenda[$v['identifier']] = [
            'title' => $v['title'],
            'identifier' => NULL,
            'amount' => 0,
          ];
        }
      }
    }

    usort($legenda, static function ($a, $b) {
      return $b['amount'] <=> $a['amount'];
    });

    return $this->jsonResponse(array_values($legenda));
  }

  /**
   * Add the amount field to the query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   Query.
   * @param string $fase
   *   Fase.
   */
  protected function addAmmountToQuery(SelectInterface $query, $fase): void {
    switch (strtoupper($fase)) {
      case 'JV':
        $query->addExpression('SUM(bt.bedrag_jaarverslag)', 'amount');
        $query->isNotNull('bt.bedrag_jaarverslag');
        break;

      case 'O1':
        $query->addExpression('SUM(bt.bedrag_suppletoire1)', 'amount');
        $query->isNotNull('bt.bedrag_suppletoire1');
        break;

      case 'O2':
        $query->addExpression('SUM(bt.bedrag_suppletoire2)', 'amount');
        $query->isNotNull('bt.bedrag_suppletoire2');
        break;

      case 'VB':
        $query->addExpression('SUM(bt.bedrag_vastgestelde_begroting)', 'amount');
        $query->isNotNull('bt.bedrag_vastgestelde_begroting');
        break;

      default:
        $query->addExpression('SUM(bt.bedrag_begroting)', 'amount');
        $query->isNotNull('bt.bedrag_begroting');
        break;
    }
    $query->orderBy('amount', 'DESC');
  }

  /**
   * Get the years which should be shown in the visual.
   *
   * @param int $jaar
   *   Jaar.
   * @param string $fase
   *   Fase.
   *
   * @return array
   *   The available years.
   */
  public function getYearsToShow($jaar, $fase): array {
    $years = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->addExpression('DISTINCT(jaar)', 'jaar');
    if (strtoupper($fase) === 'JV') {
      $query->isNotNull('bedrag_jaarverslag');
    }
    elseif (strtoupper($fase) === 'O1') {
      $query->isNotNull('bedrag_suppletoire1');
    }
    elseif (strtoupper($fase) === 'O2') {
      $query->isNotNull('bedrag_suppletoire2');
    }
    else {
      $query->isNotNull('bedrag_begroting');
    }
    $query->groupBy('jaar');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $years[] = $record['jaar'];
    }

    if (in_array(($jaar - 1), $years) && in_array(($jaar + 1), $years)) {
      return [($jaar - 1), $jaar, ($jaar + 1)];
    }
    elseif (in_array(($jaar + 2), $years)) {
      return [$jaar, ($jaar + 1), ($jaar + 2)];
    }
    elseif (in_array(($jaar - 2), $years)) {
      return [($jaar - 2), ($jaar - 1), $jaar];
    }

    return [$jaar];
  }

  /**
   * Get the total sum for a given year.
   *
   * @param int $jaar
   *   Jaar.
   * @param string $fase
   *   Fase.
   * @param string $vuo
   *   VUO.
   * @param string $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   Artikel minfin id.
   * @param string|null $artikelonderdeelMinfinId
   *   Artikelonderdeel minfin id.
   * @param string|null $instrumentOfUitsplitsingApparaatMinfinId
   *   Instrument of uitsplitsing apparaat minfin id.
   * @param string|null $regelingDetailniveauMinfinId
   *   Regeling detailniveau minfin id.
   *
   * @return int
   *   The total summ.
   */
  protected function getTotalSum($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId = NULL, $artikelonderdeelMinfinId = NULL, $instrumentOfUitsplitsingApparaatMinfinId = NULL, $regelingDetailniveauMinfinId = NULL): int {
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('bt.jaar', $jaar, '=');
    if (isset($regelingDetailniveauMinfinId)) {
      $query->condition('bt.show', 1, '=');
      $query->join('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
      $query->condition('rd.regeling_detailniveau_minfin_id', $regelingDetailniveauMinfinId, '=');
    }
    if (isset($instrumentOfUitsplitsingApparaatMinfinId)) {
      $query->join('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
      $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $instrumentOfUitsplitsingApparaatMinfinId, '=');
    }
    if (isset($artikelonderdeelMinfinId)) {
      $query->join('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
      $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
    }
    if (!empty($artikelMinfinId)) {
      $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
    }
    if (!empty($hoofdstukMinfinId)) {
      $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    }
    return (int) $query->execute()->fetchField();
  }

  /**
   * Get the requested name.
   *
   * @param int $jaar
   *   Jaar.
   * @param string $fase
   *   Fase.
   * @param string|null $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   Artikel minfin id.
   * @param string|null $artikelonderdeelMinfinId
   *   Artikelonderdeel minfin id.
   * @param string|null $instrumentOfUitsplitsingApparaatMinfinId
   *   Instrument of uitsplitsing apparaat minfin id.
   * @param string|null $regelingDetailniveauMinfinId
   *   Regeling detailniveau minfin id.
   * @param bool $previous
   *   Wheter to get the previous name or the current.
   *
   * @return string|null
   *   The name or null if no name was found.
   */
  protected function getName($jaar, $fase, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL, $artikelonderdeelMinfinId = NULL, $instrumentOfUitsplitsingApparaatMinfinId = NULL, $regelingDetailniveauMinfinId = NULL, $previous = FALSE): ?string {
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->leftJoin('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
    $query->leftJoin('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->leftJoin('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->condition('bt.jaar', $jaar, '=');

    if (!$previous && isset($regelingDetailniveauMinfinId)) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
      $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $instrumentOfUitsplitsingApparaatMinfinId, '=');
      $query->condition('rd.regeling_detailniveau_minfin_id', $regelingDetailniveauMinfinId, '=');
      $query->addField('rd', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $fase, $hoofdstukMinfinId, $artikelMinfinId, $artikelonderdeelMinfinId, $instrumentOfUitsplitsingApparaatMinfinId, NULL);
    }
    if ((!$previous && isset($instrumentOfUitsplitsingApparaatMinfinId)) || ($previous && isset($regelingDetailniveauMinfinId))) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
      $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $instrumentOfUitsplitsingApparaatMinfinId, '=');
      $query->addField('iua', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $fase, $hoofdstukMinfinId, $artikelMinfinId, $artikelonderdeelMinfinId, ($previous ? $regelingDetailniveauMinfinId : NULL), NULL, $previous);
    }
    if ((!$previous && isset($artikelonderdeelMinfinId)) || ($previous && isset($instrumentOfUitsplitsingApparaatMinfinId))) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
      $query->addField('ao', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $fase, $hoofdstukMinfinId, $artikelMinfinId, ($previous ? $artikelonderdeelMinfinId : NULL), NULL, NULL, $previous);
    }
    if ((!$previous && !empty($artikelMinfinId)) || ($previous && isset($artikelonderdeelMinfinId))) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->addField('a', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $fase, $hoofdstukMinfinId, ($previous ? $artikelMinfinId : NULL), NULL, NULL, NULL, $previous);
    }
    if ((!$previous && !empty($hoofdstukMinfinId)) || ($previous && !empty($artikelMinfinId))) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->addField('h', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
    }
    if (!$previous || ($previous && !empty($hoofdstukMinfinId))) {
      return $this->minfinNamingService->getFaseName($fase);
    }

    return NULL;
  }

  /**
   * Get the artikel links.
   *
   * @param string|null $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   Artikel minfin id.
   * @param string|null $artikelonderdeelMinfinId
   *   Artikelonderdeel minfin id.
   *
   * @return array
   *   An array with artikel links.
   */
  protected function getArtikelLinks($hoofdstukMinfinId = NULL, $artikelMinfinId = NULL, $artikelonderdeelMinfinId = NULL) {
    $links = [];

    if ($hoofdstukMinfinId && !isset($artikelonderdeelMinfinId)) {
      $query = $this->connection->select('mf_artikel_link', 'al');
      $query->fields('al', ['category', 'link', 'description']);
      if ($artikelMinfinId) {
        $query->condition('al.artikel_minfin_id', $artikelMinfinId, '=');
      }
      else {
        $query->isNull('al.artikel_minfin_id');
      }
      $query->condition('al.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $category = 'Beleidsdoorlichting';
        if ($record['category'] === 'performance information') {
          $category = 'Beleidsinformatie';
        }
        elseif ($record['category'] === 'cbs') {
          $category = 'Beleidsinformatie 2';
        }
        $links[$category][] = [
          'link' => $record['link'],
          'description' => $record['description'],
        ];
      }

      // We only want to show Performance Information links if we have no CBS
      // links.
      if (isset($links['Beleidsinformatie 2'])) {
        $links['Beleidsinformatie'] = $links['Beleidsinformatie 2'];
        unset($links['Beleidsinformatie 2']);
      }
    }

    return $links;
  }

}
