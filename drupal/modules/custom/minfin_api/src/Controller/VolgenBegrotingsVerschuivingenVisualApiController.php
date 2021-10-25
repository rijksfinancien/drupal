<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

use Drupal\Core\Url;

/**
 * The swagger API for tracking the visual budget trough the years.
 */
class VolgenBegrotingsVerschuivingenVisualApiController extends BegrotingsVisualApiController {

  /**
   * @todo Rework the call so it extends the parent call instead of completely overwriting it.
   */
  public function json($jaar, $fase, $vuo, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL, $sub1 = NULL, $sub2 = NULL, $sub3 = NULL, $triple = FALSE, $depth = 2) {
    // Check if we have any data for the given year/phase.
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('jaar', $jaar, '=');
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
            'phase' => isset($kamerstukPhases[$fase]) ? $kamerstukPhases[$fase] : $fase,
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
          $kamerstuk = $url->toString(TRUE);
        }
      }
      catch (\Exception $e) {
        // Do nothing.
      }
    }

    $data = [];
    $dataToRetrieve = [];
    $retrievedData = [];
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

      $dataToRetrieve[] = [
        'year' => $yearToShow,
        'fase' => $fase,
        'vuo' => $vuo,
        'hoofdstukMinfinId' => $hoofdstukMinfinId,
        'artikelMinfinId' => $artikelMinfinId,
        'sub1' => $sub1,
        'sub2' => $sub2,
        'sub3' => $sub3,
      ];
    }

    // Determine for which data we want to retrieve historical data.
    $trackDataTroughYears = $sub2 !== NULL;

    $originalGroupIds = NULL;
    $children = [];
    $iterator = new \ArrayIterator($dataToRetrieve);
    foreach ($iterator as $v) {
      $retrievedData[] = implode('.', $v);

      if ($v['sub3'] !== NULL) {
        $values1 = $this->getRegelingDetailniveau($v['year'], $v['fase'], $v['vuo'], $v['hoofdstukMinfinId'], $v['artikelMinfinId'], $v['sub1'], $v['sub2'], $v['sub3']);
        $values2 = $this->getVoorgaandeRegelingDetailniveau($v['year'], $v['fase'], $v['vuo'], $v['hoofdstukMinfinId'], $v['artikelMinfinId'], $v['sub1'], $v['sub2'], $v['sub3']);
      }
      elseif ($v['sub2'] !== NULL) {
        $values1 = $this->getRegelingDetailniveau($v['year'], $v['fase'], $v['vuo'], $v['hoofdstukMinfinId'], $v['artikelMinfinId'], $v['sub1'], $v['sub2']);
      }
      elseif ($v['sub1'] !== NULL) {
        $values1 = $this->getInstrumentOfUitsplitsingApparaat($v['year'], $v['fase'], $v['vuo'], $v['hoofdstukMinfinId'], $v['artikelMinfinId'], $v['sub1'], ($depth - 1));
        $values2 = [];
      }
      elseif ($v['artikelMinfinId'] !== NULL) {
        $values1 = $this->getArtikelonderdeelData($v['year'], $v['fase'], $v['vuo'], $v['hoofdstukMinfinId'], $v['artikelMinfinId'], ($depth - 1));
        $values2 = [];
      }
      elseif ($v['hoofdstukMinfinId'] !== NULL) {
        $values1 = $this->getArtikelData($v['year'], $v['fase'], $v['vuo'], $v['hoofdstukMinfinId'], ($depth - 1));
        $values2 = [];
      }
      else {
        $values1 = $this->getHoofdstukData($v['year'], $v['fase'], $v['vuo'], ($depth - 1));
        $values2 = [];
      }

      if ($originalGroupIds === NULL && $v['year'] === $jaar) {
        $originalGroupIds = array_keys($values1);
      }

      // Merge the values together.
      foreach ([$values1, $values2] as $merge) {
        foreach ($merge as $key => $val) {
          if ($trackDataTroughYears) {
            $data[$v['year']]['children'][$key] = $key;
            if (!isset($children[$key])) {
              $children[$key] = $val;
              $children[$key]['alternative_group'] = [];
            }
            else {
              $children[$key]['alternative_group'][$val['group']] = $val['group'];
            }
          }
          else {
            $data[$v['year']]['children'][$key] = $val;
          }
        }
      }

      // Loop through the data we got from the getVoorgaandeXXX tables and see
      // if we'll need to retrieve additional data to complete the dataset.
      foreach ($values2 as $new) {
        if (isset($new['trace'])) {
          $explode = explode('/', $new['trace']);
          foreach ($years as $yearToShow) {
            $newArray = [
              'year' => $yearToShow,
              'fase' => $v['fase'],
              'vuo' => $v['vuo'],
              'hoofdstukMinfinId' => $explode[1] ?? NULL,
              'artikelMinfinId' => $explode[2] ?? NULL,
              'sub1' => $explode[3] ?? NULL,
              'sub2' => $explode[4] ?? NULL,
              'sub3' => $explode[5] ?? NULL,
            ];
            if ($yearToShow !== $v['year'] && !in_array(implode('.', $newArray), $retrievedData, TRUE) && !in_array($newArray, $iterator->getArrayCopy(), TRUE)) {
              $iterator->append($newArray);
            }
          }
        }
      }
    }

    if ($trackDataTroughYears) {
      // Map the correct groupId to each child.
      foreach ($originalGroupIds ?? [] as $groupId) {
        $iterator = new \ArrayIterator();
        if (isset($children[$groupId]['alternative_group'])) {
          foreach ($children[$groupId]['alternative_group'] ?? [] as $group) {
            $iterator->append($group);
          }
          unset($children[$groupId]['alternative_group']);
          foreach ($iterator as $id) {
            $children[$id]['group'] = $groupId;
            foreach ($children[$id]['alternative_group'] ?? [] as $group) {
              $iterator->append($group);
            }
            unset($children[$id]['alternative_group']);
          }
        }
      }

      // Return the children to the correct location in the $data array.
      foreach ($years as $year) {
        foreach ($data[$year]['children'] as $id) {
          unset($children[$id]['alternative_group']);
          $data[$year]['children'][$id] = $children[$id];
        }
      }
    }

    // Only return a cached version if sub1 is empty.
    if ($v['sub1'] === NULL) {
      $cacheMetadata = [
        'contexts' => [
          'url',
        ],
        'tags' => ['minfin_import:budgettaire_tabellen', 'minfin_import:budgettaire_tabellen_history'],
      ];
      return $this->cacheableJsonResponse($data, $cacheMetadata);
    }
    return $this->jsonResponse($data, !empty($data[$jaar]['children']) ? 200 : 404);
  }

  /**
   * @todo Rework the original calls so they all work based on the output of this call.
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
      $record['trace'] = '/' . $hoofdstukMinfinId . '/' . $artikelMinfinId . '/' . $sub1 . '/' . $record['identifier'];
      $record['link'] = $record['trace'];
      $record['amount'] = (int) $record['amount'];
      if ($depth > 0) {
        $record['children'] = $this->getRegelingDetailniveau($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $record['identifier']);
      }
      $data[] = $record;
    }

    return $data;
  }

  /**
   * @todo Rework the original calls so they all work based on the output of this call.
   */
  protected function getRegelingDetailniveau($jaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2, $sub3 = NULL): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->join('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->join('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->join('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
    $query->addField('rd', 'naam', 'title');
    $query->addField('rd', 'regeling_detailniveau_minfin_id', 'trace');
    $query->addField('bt', 'btabel_minfin_id', 'identifier');
    $query->addField('bt', 'btabel_minfin_id', 'group');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('bt.jaar', $jaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
    $query->condition('ao.artikelonderdeel_minfin_id', $sub1);
    $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $sub2);
    if ($sub3 !== NULL) {
      $query->condition('rd.regeling_detailniveau_minfin_id', $sub3, '=');
    }
    $query->groupBy('bt.btabel_minfin_id');
    $query->groupBy('rd.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $query->groupBy('a.artikel_minfin_id');
    $query->groupBy('ao.artikelonderdeel_minfin_id');
    $query->groupBy('iua.instrument_of_uitsplitsing_apparaat_minfin_id');
    $query->groupBy('rd.regeling_detailniveau_minfin_id');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['trace'] = '/' . $hoofdstukMinfinId . '/' . $artikelMinfinId . '/' . $sub1 . '/' . $sub2 . '/' . $record['trace'];
      if ($sub3 === NULL) {
        $record['link'] = $record['trace'];
      }

      $record['amount'] = (int) $record['amount'];
      $data[$record['identifier']] = $record;
    }

    return $data;
  }

  /**
   * Call which will retrieve data based on the mf_voorgaand_regeling_detailniveau table.
   */
  protected function getVoorgaandeRegelingDetailniveau($huidigJaar, $fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2, $sub3 = NULL): array {
    $data = [];
    $query = $this->connection->select('mf_b_tabel', 'vbt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = vbt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = vbt.artikel_id');
    $query->join('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = vbt.artikelonderdeel_id');
    $query->join('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = vbt.instrument_of_uitsplitsing_apparaat_id');
    $query->join('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = vbt.regeling_detailniveau_id');
    $query->join('mf_voorgaand_regeling_detailniveau', 'trace', '(trace.voorgaand_regeling_detailniveau_id = vbt.regeling_detailniveau_id)');
    $query->join('mf_b_tabel', 'bt', '(bt.regeling_detailniveau_id = trace.regeling_detailniveau_id)');
    $query->join('mf_hoofdstuk', 'vh', 'vh.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'va', 'va.artikel_id = bt.artikel_id');
    $query->join('mf_artikelonderdeel', 'vao', 'vao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->join('mf_instrument_of_uitsplitsing_apparaat', 'viua', 'viua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->join('mf_regeling_detailniveau', 'vrd', 'vrd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
    $query->addField('vrd', 'naam', 'title');
    $query->addField('vh', 'hoofdstuk_minfin_id', 'trace');
    $query->addField('va', 'artikel_minfin_id', 'trace2');
    $query->addField('vao', 'artikelonderdeel_minfin_id', 'trace3');
    $query->addField('viua', 'instrument_of_uitsplitsing_apparaat_minfin_id', 'trace4');
    $query->addField('vrd', 'regeling_detailniveau_minfin_id', 'trace5');
    $query->addField('bt', 'btabel_minfin_id', 'identifier');
    $query->addField('vbt', 'btabel_minfin_id', 'group');
    $query->addField('trace', 'type', 'type');
    $this->addAmmountToQuery($query, $fase);
    $query->condition('trace.fase', $fase, '=');
    $query->condition('trace.voorgaand_fase', $fase, '=');
    $query->condition('bt.jaar', $huidigJaar, '=');
    $query->condition('bt.vuo', $vuo, '=');
    $query->condition('vbt.vuo', $vuo, '=');
    $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
    $query->condition('ao.artikelonderdeel_minfin_id', $sub1, '=');
    $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $sub2, '=');
    if ($sub3 !== NULL) {
      $query->condition('rd.regeling_detailniveau_minfin_id', $sub3, '=');
    }
    $query->groupBy('trace.type');
    $query->groupBy('vrd.naam');
    $query->groupBy('h.hoofdstuk_minfin_id');
    $query->groupBy('a.artikel_minfin_id');
    $query->groupBy('ao.artikelonderdeel_minfin_id');
    $query->groupBy('iua.instrument_of_uitsplitsing_apparaat_minfin_id');
    $query->groupBy('bt.btabel_minfin_id');
    $query->groupBy('vbt.btabel_minfin_id');
    $query->groupBy('vh.hoofdstuk_minfin_id');
    $query->groupBy('va.artikel_minfin_id');
    $query->groupBy('vao.artikelonderdeel_minfin_id');
    $query->groupBy('viua.instrument_of_uitsplitsing_apparaat_minfin_id');
    $query->groupBy('vrd.regeling_detailniveau_minfin_id');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $record['trace'] = '/' . $record['trace'] . '/' . $record['trace2'] . '/' . $record['trace3'] . '/' . $record['trace4'] . '/' . $record['trace5'];
      unset($record['trace2'], $record['trace3'], $record['trace4'], $record['trace5']);
      if ($sub3 === NULL) {
        $record['link'] = $record['trace'];
      }

      $record['amount'] = (int) $record['amount'];
      $data[$record['identifier']] = $record;
    }

    return $data;
  }

}
