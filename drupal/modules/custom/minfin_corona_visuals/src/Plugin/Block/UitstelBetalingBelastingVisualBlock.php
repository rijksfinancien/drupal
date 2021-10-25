<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

/**
 * Provides a 'UitstelBetalingBelastingVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_uitstel_betaling_belasting_visual_block",
 *   admin_label = @Translation("Corona visual - Uitstel van betaling voor belastingen"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class UitstelBetalingBelastingVisualBlock extends CoronaVisualBaseBlock {

  /**
   * Builds the uitstel van betaling voor belastingen visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_uitstel_betaling_belasting_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('corona_visual', 'belastinguitstel'),
            ],
          ],
        ],
      ],
    ];
  }

}
