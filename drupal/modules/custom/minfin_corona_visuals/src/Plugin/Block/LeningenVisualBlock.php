<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\minfin_visuals\Plugin\Block\VisualBaseBlock;

/**
 * Provides a 'LeningenVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_leningen_visual_block",
 *   admin_label = @Translation("Corona visual - Leningen"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class LeningenVisualBlock extends VisualBaseBlock {

  /**
   * Builds the leningen visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_leningen_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('corona_visual', 'leningen'),
            ],
          ],
        ],
      ],
    ];
  }

}
