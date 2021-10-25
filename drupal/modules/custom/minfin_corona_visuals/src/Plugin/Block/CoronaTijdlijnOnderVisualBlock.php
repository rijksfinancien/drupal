<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'CoronaTijdlijnOnderVisualBlockVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_visuals_corona_tijdlijn_onder_visual_block",
 *   admin_label = @Translation("Corona visual - Corona tijdlijn onder"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class CoronaTijdlijnOnderVisualBlock extends BlockBase {

  /**
   * Builds the corona tijdlijn onder visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_corona_tijdlijn_onder_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
      ],
    ];
  }

}
