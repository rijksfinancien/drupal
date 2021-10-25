<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Bouwstenen belastingstelsel" visualisation.
 *
 * @Block(
 *   id = "bouwstenen_belastingstelsel",
 *   admin_label = "Rijksfinancien: Bouwstenen voor een beter belastingstelsel",
 * )
 */
class BouwstenenBelastingstelselBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/bouwstenen_belastingstelsel',
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
