<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

/**
 * Provides a 'FiscaleMaatregelenVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_fiscale_maatregelen_visual_block",
 *   admin_label = @Translation("Corona visual - Fiscale maatregelen"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class FiscaleMaatregelenVisualBlock extends CoronaVisualBaseBlock {

  /**
   * Builds the fiscale maatregelen visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_fiscale_maatregelen_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('corona_visual', 'fiscalemaatregelen'),
            ],
          ],
        ],
      ],
    ];
  }

}
