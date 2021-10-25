<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Begroting, Premie & Fiscaal" visualisation.
 *
 * @Block(
 *   id = "begroting_premie_fiscaal",
 *   admin_label = "Rijksfinancien: Begroting, Premie & Fiscaal",
 * )
 */
class BegrotingPremieFiscaalBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_begroting_premie_fiscaal',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/begroting_premie_fiscaal',
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
