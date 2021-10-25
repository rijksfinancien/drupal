<?php

// phpcs:disable
// Imported old code from rijksfinancien.
namespace Drupal\rijksfinancien_visuals\Controller;

/**
 * Begroting vs miljoenennota API.
 */
class BegrotingVsMiljoenennotaController extends BaseApiController {

  // @todo: Replace these data files with a real API.
  public function getData($year) {
    $data = [];
    if ($year === '2019' || $year === '2020') {
      $file = drupal_get_path('module', 'rijksfinancien_visuals') . '/assets/begroting_vs_miljoenennota/data/' . $year . '.json';
      $data = json_decode(file_get_contents($file));
    }

    return $this->jsonResponse($data);
  }

}
