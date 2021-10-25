<?php

declare(strict_types = 1);

namespace Drupal\minfin_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'VerzelfstandigingVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_visuals_verzelfstandiging_visual_block",
 *   admin_label = @Translation("MINFIN: Verzelfstandiging"),
 *   category = @Translation("MINFIN visuals"),
 * )
 */
class VerzelfstandigingVisualBlock extends BlockBase {

  /**
   * Builds the uitgavenplafonds visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'visual_verzelfstandiging',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
