<?php

declare(strict_types = 1);

namespace Drupal\minfin_visuals\Plugin\Block;

use Drupal\Core\Url;

/**
 * Provides a 'Wie Ontvingen' Block.
 *
 * @Block(
 *   id = "minfin_visuals_wie_ontvingen_visual_block",
 *   admin_label = @Translation("MINFIN: Wie Ontvingen"),
 *   category = @Translation("MINFIN visuals"),
 * )
 */
class WieOntvingenVisualBlock extends VisualBaseBlock {

  /**
   * Builds the 'Wie Ontvingen' visual.
   *
   * @return array
   *   The render array.
   */
  public function build(): array {
    $apiUrl = NULL;
    $getParams = $this->request->query->all();

    // If we don't have a referrer show visual part 1.
    if (!isset($getParams['referrer'], $getParams['referrer_id']) || !$this->allowedReferrerTypes($getParams['referrer'])) {
      try {
        $parts = explode('/', Url::fromRoute('<current>')->toString());
        $type = end($parts);
        if (!$this->allowedPathTypes($type)) {
          $type = 'ontvangers';
        }
        $apiUrl = Url::fromRoute('minfin_api_public.csv.financiele_instrumenten.' . $type)->toString();
      }
      catch (\Exception $e) {
        return [];
      }

      return [
        '#theme' => 'visual_wie_ontvingen_start',
        '#attached' => [
          'library' => ['minfin_visuals/visuals'],
          'drupalSettings' => [
            'minfin' => [
              'visual_download' => [
                'api' => $apiUrl,
              ],
            ],
          ],
        ],
      ];
    }

    $apiUrl = '/open-data/api/json/v2/financiele_instrumenten?' . http_build_query($getParams);

    return [
      '#theme' => 'visual_wie_ontvingen',
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'api' => $apiUrl,
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Check if the given type is allowed for a route.
   *
   * @param string $type
   *   The type.
   *
   * @return bool
   *   If the given type is allowed.
   */
  private function allowedPathTypes(string $type): bool {
    return in_array($type, [
      'artikelen',
      'hoofdstukken',
      'ontvangers',
      'regelingen',
    ]);
  }

  /**
   * Check if the given type is allowed for a referrer.
   *
   * @param string $type
   *   The type.
   *
   * @return bool
   *   If the given type is allowed.
   */
  private function allowedReferrerTypes(string $type): bool {
    return in_array($type, [
      'artikel',
      'hoofdstuk',
      'ontvanger',
      'regeling',
    ]);
  }

}
