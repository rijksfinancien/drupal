<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ASUitgavenVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_as_uitgaven_visaul_block",
 *   admin_label = @Translation("Corona visual - Automatische stabilisatoren uitgaven"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class ASUitgavenVisualBlock extends BlockBase {

  /**
   * Builds the AS uitgaven visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_as_uitgaven_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
