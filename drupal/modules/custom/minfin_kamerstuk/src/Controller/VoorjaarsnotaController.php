<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for voorjaarsnota pages.
 */
class VoorjaarsnotaController extends NotaBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'voorjaarsnota';
  }

  /**
   * {@inheritdoc}
   */
  protected function getName(): string {
    return 'Voorjaarsnota';
  }

  /**
   * {@inheritdoc}
   */
  protected function getPhase(): string {
    return '1SUPP';
  }

}
