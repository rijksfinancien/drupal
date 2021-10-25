<?php

namespace Drupal\minfin_api_public\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a documentation block.
 *
 * @Block(
 *  id = "api_documentation_block",
 *  admin_label = @Translation("Documentation Block"),
 * )
 */
class DocumentationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The base url.
   *
   * @var string|null
   */
  protected $baseUrl;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $pluginId
   *   The block id.
   * @param mixed $pluginDefinition
   *   The block definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, RequestStack $requestStack) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    if ($request = $requestStack->getCurrentRequest()) {
      $this->baseUrl = $request->getSchemeAndHttpHost();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('request_stack')
    );
  }

  /**
   * Build the block.
   *
   * @return array
   *   Returns render array.
   */
  public function build(): array {
    $build['swagger'] = [
      '#type' => 'container',
      '#attributes' => [
        'lang' => 'en',
        'class' => ['container'],
        'id' => 'swagger-ui',
      ],
      '#attached' => [
        'library' => [
          'minfin_api/api',
        ],
        'drupalSettings' => [
          'minfin_api' => [
            'url' => $this->baseUrl . '/swagger.json',
          ],
        ],
      ],
    ];
    $build['#cache']['max-age'] = 0;

    return $build;
  }

}
