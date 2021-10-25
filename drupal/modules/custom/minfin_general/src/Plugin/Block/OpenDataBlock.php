<?php

namespace Drupal\minfin_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a open data block.
 *
 * @Block(
 *  id = "general_open_data_block",
 *  admin_label = @Translation("Open Data Block"),
 * )
 */
class OpenDataBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The file storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The image style storage.
   *
   * @var \Drupal\image\ImageStyleStorage
   */
  protected $imageStyleStorage;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * BannerBlock constructor.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $pluginId
   *   The block id.
   * @param mixed $pluginDefinition
   *   The block definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager to get different storages.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match to check the current route.
   * @param \Drupal\Core\Image\ImageFactory $imageFactory
   *   The image factory to get the image file for the banner.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory to get the needed config.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch, ImageFactory $imageFactory, ConfigFactoryInterface $configFactory, MinfinServiceInterface $minfinService, FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->imageStyleStorage = $entityTypeManager->getStorage('image_style');
    $this->routeMatch = $routeMatch;
    $this->imageFactory = $imageFactory;
    $this->minfinService = $minfinService;
    $this->formBuilder = $formBuilder;

    // Get config.
    $this->config = $configFactory->get('minfin_general.open_data.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('image.factory'),
      $container->get('config.factory'),
      $container->get('minfin.minfin'),
      $container->get('form_builder')
    );
  }

  /**
   * Build the block.
   *
   * @return array
   *   Returns render array.
   */
  public function build(): array {
    // Set theme.
    $build = [
      '#theme' => 'open_data_block',
    ];

    if ($config = $this->config->get('open_data_block')) {
      for ($i = 1; $i < 4; $i++) {
        $imageFid = $config['column_' . $i]['image'][0];

        if ($imageFid && ($file = $this->fileStorage->load($imageFid))) {

          // Validate the image.
          $fileUri = $file->getFileUri();
          $columns[$i]['image'] = $this->imageFactory->get($fileUri);
        }

        if ($config['column_' . $i]['url'] && ($entity = $this->nodeStorage->load($config['column_' . $i]['url'])) && $url = $entity->toUrl('canonical')) {
          $columns[$i]['url'] = $url;
        }

        $columns[$i]['title'] = $config['column_' . $i]['title'];
        $columns[$i]['description'] = $config['column_' . $i]['description'];

      }

      $build += [
        '#title' => $config['main_title'],
        '#columns' => $columns,
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeTags(parent::getCacheContexts(), ['url']);
  }

}
