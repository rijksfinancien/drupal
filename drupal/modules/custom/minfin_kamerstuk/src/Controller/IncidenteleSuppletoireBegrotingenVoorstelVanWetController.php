<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for voorstel van wet pages.
 */
class IncidenteleSuppletoireBegrotingenVoorstelVanWetController extends IncidenteleSuppletoireBegrotingenController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'isb_memorie_van_toelichting';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDatabaseType(): string {
    return 'isb_voorstel_van_wet';
  }

}
