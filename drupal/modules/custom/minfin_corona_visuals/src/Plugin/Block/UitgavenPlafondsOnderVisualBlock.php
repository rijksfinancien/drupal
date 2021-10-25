<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'UitgavenPlafondsOnderVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_uitgaven_plafonds_onder_visual_block",
 *   admin_label = @Translation("Corona visual - Uitgavenplafonds onder"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class UitgavenPlafondsOnderVisualBlock extends BlockBase {

  /**
   * Builds the uitgavenplafonds onder visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_uitgaven_plafonds_onder_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
