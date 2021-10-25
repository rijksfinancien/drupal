<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Begroting vs Miljoenennota" visualisation.
 *
 * @Block(
 *   id = "begroting_vs_miljoenennota",
 *   admin_label = "Rijksfinancien: Begroting vs Miljoenennota",
 * )
 */
class BegrotingVsMiljoenennotaBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/begroting_vs_miljoenennota',
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
