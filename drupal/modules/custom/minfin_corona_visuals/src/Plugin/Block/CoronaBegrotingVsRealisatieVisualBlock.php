<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'CoronaBegrotingVsRealisatieVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_corona_begroting_vs_realisatie_visual_block",
 *   admin_label = @Translation("Corona visual - Corona begroting vs realisatie"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class CoronaBegrotingVsRealisatieVisualBlock extends BlockBase {

  /**
   * Builds the corona begroting vs realisatie visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_corona_begroting_vs_realisatie_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
