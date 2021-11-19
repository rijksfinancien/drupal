<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\minfin_visuals\Plugin\Block\VisualBaseBlock;

/**
 * Provides a 'NoodmaatregelenVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_noodmaatregelen_visual_block",
 *   admin_label = @Translation("Corona visual - Noodmaatregelen"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class NoodmaatregelenVisualBlock extends VisualBaseBlock {

  /**
   * Builds the noodmaatregelen visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_noodmaatregelen_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('corona_visual', 'uitgavenmaatregelen'),
            ],
          ],
        ],
      ],
    ];
  }

}
