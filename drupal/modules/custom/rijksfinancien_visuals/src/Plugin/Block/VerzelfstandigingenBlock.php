<?php

namespace Drupal\rijksfinancien_visuals\Plugin\Block;

use Drupal\Core\Cache\Cache;

/**
 * Provides a block for the "Verzelfstandigingen" visualisation.
 *
 * @Block(
 *   id = "verzelfstandigingen",
 *   admin_label = "Rijksfinancien: Verzelfstandigingen",
 * )
 */
class VerzelfstandigingenBlock extends RijksfinancienVisualBaseBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'rijksfinancien_visual_verzelfstandigingen',
      '#attached' => [
        'library' => [
          'minfin_visuals/visuals_download',
          'rijksfinancien_visuals/verzelfstandigingen',
        ],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('verzelfstandigingen'),
            ],
          ],
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
