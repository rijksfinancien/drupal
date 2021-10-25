<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for financieel jaarverslag pages.
 */
class FinancieelJaarverslagController extends NotaBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'financieel_jaarverslag';
  }

  /**
   * {@inheritdoc}
   */
  protected function getName(): string {
    return 'Financieel jaarverslag';
  }

  /**
   * {@inheritdoc}
   */
  protected function getPhase(): string {
    return 'JV';
  }

}
