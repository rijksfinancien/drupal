<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Fiscale regelingen" visualisation.
 *
 * @Block(
 *   id = "fiscale_regelingen",
 *   admin_label = "Rijksfinancien: Fiscale regelingen",
 * )
 */
class FiscaleRegelingenBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/fiscale_regelingen',
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
