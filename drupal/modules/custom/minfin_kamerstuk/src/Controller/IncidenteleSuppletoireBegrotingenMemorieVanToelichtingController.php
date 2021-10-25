<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for isb memorie van toelichting pages.
 */
class IncidenteleSuppletoireBegrotingenMemorieVanToelichtingController extends IncidenteleSuppletoireBegrotingenController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'isb_memorie_van_toelichting';
  }

}
