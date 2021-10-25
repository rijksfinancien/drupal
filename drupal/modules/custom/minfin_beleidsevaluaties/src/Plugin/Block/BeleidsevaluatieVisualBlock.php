<?php

declare(strict_types = 1);

namespace Drupal\minfin_beleidsevaluaties\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'BeleidsevaluatieVisual' Block.
 *
 * @Block(
 *   id = "minfin_beleidsevaluatie_visual_block",
 *   admin_label = @Translation("MINFIN beleidsevaluatie visual block"),
 *   category = @Translation("MINFIN beleidsevaluatie"),
 * )
 */
class BeleidsevaluatieVisualBlock extends BlockBase {

  /**
   * Builds the beleidsevaluatie visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'beleidsevaluatie_visual_block',
      '#attached' => [
        'library' => ['minfin_beleidsevaluaties/beleidsevaluatie_visual_block'],
      ],
    ];
  }

}
