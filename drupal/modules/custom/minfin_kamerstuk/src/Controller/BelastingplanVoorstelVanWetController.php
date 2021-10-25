<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for belastingplan voorstel van wet pages.
 */
class BelastingplanVoorstelVanWetController extends NotaBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'belastingplan_voorstel_van_wet';
  }

  /**
   * {@inheritdoc}
   */
  protected function getName(): string {
    return 'Belastingplan';
  }

  /**
   * {@inheritdoc}
   */
  protected function getPhase(): string {
    return 'OWB';
  }

}
