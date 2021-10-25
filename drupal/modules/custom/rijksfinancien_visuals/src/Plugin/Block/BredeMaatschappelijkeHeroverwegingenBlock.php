<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Wie ontvingen" visualisation.
 *
 * @Block(
 *   id = "brede_maatschappelijke_heroverwegingen",
 *   admin_label = "Rijksfinancien: Brede Maatschappelijke Heroverwegingen",
 * )
 */
class BredeMaatschappelijkeHeroverwegingenBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual',
      '#attached' => [
        'library' => [
          'rijksfinancien_visuals/brede_maatschappelijke_herowverwegingen',
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
