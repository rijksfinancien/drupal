<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for belastingplan memorie van toelichting pages.
 */
class BelastingplanMemorieVanToelichtingController extends NotaBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'belastingplan_memorie_van_toelichting';
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
