<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'CoronaTijdlijnVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_corona_tijdlijn_visual_block",
 *   admin_label = @Translation("Corona visual - Corona tijdlijn"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class CoronaTijdlijnVisualBlock extends BlockBase {

  /**
   * Builds the corona tijdlijn visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_corona_tijdlijn_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
