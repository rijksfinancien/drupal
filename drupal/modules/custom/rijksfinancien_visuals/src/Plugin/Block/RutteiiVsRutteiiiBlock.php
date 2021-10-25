<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Rutte II vs Rutte III" visualisation.
 *
 * @Block(
 *   id = "rutteii_vs_rutte_iii",
 *   admin_label = "Rijksfinancien: Rutte II vs Rutte III",
 * )
 */
class RutteiiVsRutteiiiBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual_rutteii_vs_rutte_iii',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/rutteii_vs_rutte_iii',
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
