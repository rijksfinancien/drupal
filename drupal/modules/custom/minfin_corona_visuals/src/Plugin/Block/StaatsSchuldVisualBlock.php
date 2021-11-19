<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\minfin_visuals\Plugin\Block\VisualBaseBlock;

/**
 * Provides a 'StaatsSchuldVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_staats_schuld_visual_block",
 *   admin_label = @Translation("Corona visual - Staatsschuld"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class StaatsSchuldVisualBlock extends VisualBaseBlock {

  /**
   * Builds the staatsschuld visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_staats_schuld_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('corona_visual', 'emu_schuld'),
            ],
          ],
        ],
      ],
    ];
  }

}
