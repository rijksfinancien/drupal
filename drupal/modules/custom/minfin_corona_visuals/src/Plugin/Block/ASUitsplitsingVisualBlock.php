<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ASUitsplitsingVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_as_uitsplitsing_visual_block",
 *   admin_label = @Translation("Corona visual - Automatische stabilisatoren uitsplitsing"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class ASUitsplitsingVisualBlock extends BlockBase {

  /**
   * Builds the AS uitsplitsing visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_as_uitsplitsing_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
