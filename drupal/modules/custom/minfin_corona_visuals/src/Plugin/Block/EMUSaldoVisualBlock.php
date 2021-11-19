<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\minfin_visuals\Plugin\Block\VisualBaseBlock;

/**
 * Provides a 'EMUSaldoVisualBlock' Block.
 *
 * @Block(
 *   id = "minfin_corona_emu_saldo_visual_block",
 *   admin_label = @Translation("Corona visual - EMU saldo"),
 *   category = @Translation("MINFIN corona visuals"),
 * )
 */
class EMUSaldoVisualBlock extends VisualBaseBlock {

  /**
   * Builds the EMU saldo visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    return [
      '#theme' => 'corona_visual_emu_saldo_block',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'source' => $this->getSourceFile('corona_visual', 'emu_saldo'),
            ],
          ],
        ],
      ],
    ];
  }

}
