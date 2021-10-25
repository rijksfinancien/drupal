<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ASInkomstenVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_as_inkomsten_visual_block",
 *   admin_label = @Translation("Corona visual - Automatische stabilisatoren inkomsten"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class ASInkomstenVisualBlock extends BlockBase {

  /**
   * Builds the AS inkomsten visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_as_inkomsten_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
