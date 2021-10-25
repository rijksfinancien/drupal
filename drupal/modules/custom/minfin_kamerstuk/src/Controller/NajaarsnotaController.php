<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for najaarsnota pages.
 */
class NajaarsnotaController extends NotaBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'najaarsnota';
  }

  /**
   * {@inheritdoc}
   */
  protected function getName(): string {
    return 'Najaarsnota';
  }

  /**
   * {@inheritdoc}
   */
  protected function getPhase(): string {
    return '2SUPP';
  }

}
