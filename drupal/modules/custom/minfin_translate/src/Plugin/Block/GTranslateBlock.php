<?php

namespace Drupal\minfin_translate\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a GTranslate Block.
 *
 * @Block(
 *   id = "minfin_translate_gtranslate_block",
 *   admin_label = @Translation("GTranslate")
 * )
 */
class GTranslateBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build['#attached']['library'][] = 'minfin_translate/gtranslate';

    $build['button']['element'] = [
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#value' => 'Translate',
      '#attributes' => [
        'href' => '#',
        'class' => ['gtranslate'],
        'role' => 'button',
        'aria-pressed' => 'false',
      ],
    ];

    $build['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['gtranslate-container'],
      ],
    ];

    $build['container']['header'] = [
      '#type' => 'container',
      '#value' => 'Translate',
      '#attributes' => [
        'class' => ['gtranslate-header'],
      ],
    ];

    $build['container']['header']['title'] = [
      '#plain_text' => 'Translate',
    ];

    $build['container']['header']['close'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#value' => 'x',
      '#attributes' => [
        'aria-label' => $this->t('Close'),
        'class' => ['gtranslate-close'],
      ],
    ];

    $build['container']['body'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['gtranslate-body'],
      ],
    ];

    $build['container']['body']['disclaimer'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => 'Use Google to translate this website. We take no responsibility for the accuracy of the translation.',
    ];

    $build['container']['body']['element'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'id' => 'google_translate_element',
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
