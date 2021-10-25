<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Ombuigings- en intensiveringslijst" visualisation.
 *
 * @Block(
 *   id = "ombuigings_en_intensiveringslijst",
 *   admin_label = "Rijksfinancien: Ombuigings- en intensiveringslijst",
 * )
 */
class OmbuigingsEnIntensiveringslijstBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/ombuigings_en_intensiveringslijst',
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
