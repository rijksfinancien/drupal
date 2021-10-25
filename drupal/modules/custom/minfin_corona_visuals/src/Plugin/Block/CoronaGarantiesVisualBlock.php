<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

/**
 * Provides a 'CoronaGarantiesVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_corona_garanties_visual_block",
 *   admin_label = @Translation("Corona visual - Corona garanties"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class CoronaGarantiesVisualBlock extends CoronaVisualBaseBlock {

  /**
   * Builds the corona garanties visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_corona_garanties_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('corona_visual', 'garanties'),
            ],
          ],
        ],
      ],
    ];
  }

}
