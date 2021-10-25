<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'UitgavenPlafondsVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_uitgaven_plafonds_visual_block",
 *   admin_label = @Translation("Corona visual - Uitgavenplafonds"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class UitgavenPlafondsVisualBlock extends BlockBase {

  /**
   * Builds the uitgavenplafonds visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_uitgaven_plafonds_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
