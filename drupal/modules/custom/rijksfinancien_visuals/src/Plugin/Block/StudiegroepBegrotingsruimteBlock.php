<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Studiegroep begrotingsruimte" visualisation.
 *
 * @Block(
 *   id = "studiegroep_begrotingsruimte",
 *   admin_label = "Rijksfinancien: Studiegroep begrotingsruimte",
 * )
 */
class StudiegroepBegrotingsruimteBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/studiegroep_begrotingsruimte',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path', 'languages']);
  }

}
