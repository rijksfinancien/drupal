<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Begroting vs realisatie" visualisation.
 *
 * @Block(
 *   id = "begroting_vs_realisatie",
 *   admin_label = "Rijksfinancien: Begroting vs realisatie",
 * )
 */
class BegrotingVsRealisatieBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual_begroting_vs_realisatie',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/begroting_vs_realisatie',
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
