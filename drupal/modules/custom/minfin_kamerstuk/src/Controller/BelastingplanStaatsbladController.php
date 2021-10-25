<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for belastingplan staatsblad pages.
 */
class BelastingplanStaatsbladController extends NotaBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'belastingplan_staatsblad';
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
