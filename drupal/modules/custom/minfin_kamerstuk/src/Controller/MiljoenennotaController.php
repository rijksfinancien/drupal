<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for miljoenennota pages.
 */
class MiljoenennotaController extends NotaBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'miljoenennota';
  }

  /**
   * {@inheritdoc}
   */
  protected function getName(): string {
    return 'Miljoenennota';
  }

  /**
   * {@inheritdoc}
   */
  protected function getPhase(): string {
    return 'OWB';
  }

}
