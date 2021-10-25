<?php

namespace Drupal\minfin_kamerstuk\Controller;

/**
 * Controller for jaarverslag pages.
 */
class JaarverslagController extends ComponentBaseController {

  /**
   * {@inheritdoc}
   */
  protected function getType(): string {
    return 'jaarverslag';
  }

}
