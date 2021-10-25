<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for voorstel van wet pages.
 */
class VoorstelVanWetController extends ComponentBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'memorie_van_toelichting';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDatabaseType(): string {
    return 'voorstel_van_wet';
  }

}
