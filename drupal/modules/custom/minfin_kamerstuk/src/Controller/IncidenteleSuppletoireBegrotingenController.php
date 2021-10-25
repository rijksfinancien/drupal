<?php

namespace Drupal\minfin_kamerstuk\Controller;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller for incidentele suppletoire begrotingen pages.
 */
abstract class IncidenteleSuppletoireBegrotingenController extends ComponentBaseController {

  /**
   * Get the title.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   *
   * @return string
   *   The title.
   */
  public function getTitle(int $year, string $phase, string $hoofdstukMinfinId): string {
    return $this->minfinNamingService->getIsbName($phase, $year, $hoofdstukMinfinId);
  }

  /**
   * {@inheritdoc}
   */
  public function buildOverview(int $year, string $phase): array {
    $type = $this->getType();
    $hoofdstukken = [];
    foreach ($this->getBuildOverviewData($type, $year, $phase) as $hoofdstukMinfinId => $values) {
      foreach ($values['isb'] as $isb) {
        $url = Url::fromRoute('minfin.isb_memorie_van_toelichting.table_of_contents', [
          'year' => $year,
          'phase' => $isb,
          'hoofdstukMinfinId' => $hoofdstukMinfinId,
        ]);

        $hoofdstukken[$hoofdstukMinfinId]['title'] = $values['naam'];
        if ($url->access()) {
          $hoofdstukken[$hoofdstukMinfinId]['links'][] = Link::fromTextAndUrl($this->minfinNamingService->getIsbName($isb, $year, $hoofdstukMinfinId), $url);
        }
      }
    }

    return [
      '#theme' => 'minfin_chapter_list_isb',
      '#items' => $hoofdstukken,
      '#prefix_text' => $this->getKamerstukkenOverviewText($type . '_' . $phase, 'prefix', ['[year]' => $year]),
      '#suffix_text' => $this->getKamerstukkenOverviewText($type . '_' . $phase, 'suffix', ['[year]' => $year]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getBuildOverviewData(string $type, int $year, string $phase): array {
    $data = [];
    $query = $this->connection->select('mf_kamerstuk', 'k');
    $query->leftJoin('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = k.hoofdstuk_minfin_id AND h.jaar = k.jaar');
    $query->fields('k', ['hoofdstuk_minfin_id', 'fase']);
    $query->fields('h', ['naam']);
    $query->distinct(TRUE);
    $query->condition('k.type', $type, '=');
    $query->condition('k.jaar', $year, '=');
    $query->condition('k.fase', $phase . '%', 'LIKE');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $k = (int) substr($record['fase'], 3);
      $data[$record['hoofdstuk_minfin_id']]['naam'] = $record['naam'];
      $data[$record['hoofdstuk_minfin_id']]['isb'][$k] = $record['fase'];
    }

    foreach ($data as &$v) {
      ksort($v['isb']);
    }

    $sort = $this->config('minfin.chapter_sorting')->get('chapters') ?? [];
    uksort($data, static function ($key1, $key2) use ($sort) {
      return ($sort[$key1] > $sort[$key2] ? 1 : -1);
    });

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOverviewAccess(int $year, string $phase): AccessResultInterface {
    $query = $this->connection->select('mf_kamerstuk', 'k');
    $query->condition('k.type', $this->getType(), '=');
    $query->condition('k.jaar', $year, '=');
    $query->condition('k.fase', $phase . '%', 'LIKE');
    if ((bool) $query->countQuery()->execute()->fetchField()) {
      return new AccessResultAllowed();
    }
    return new AccessResultForbidden();
  }

}
