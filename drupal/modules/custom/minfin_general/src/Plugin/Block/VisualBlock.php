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
use Drupal\minfin_general\Form\ChapterSelectForm;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a visual block.
 *
 * @Block(
 *  id = "general_visual_block",
 *  admin_label = @Translation("Minfin visual block"),
 * )
 */
class VisualBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $this->config = $configFactory->get('minfin_general.banner.settings');
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
      '#theme' => 'visual_block',
    ];

    // Load node from route.
    $node = $this->routeMatch->getParameter('node');
    if (is_numeric($node)) {
      $node = $this->nodeStorage->load($node);
    }

    // Retrieve image from node if it has the banner image field.
    $imageFid = NULL;
    if ($node instanceof Node && $node->hasField('field_banner_image')) {

      if ($nodeImage = $node->get('field_banner_image')->getValue()) {
        $imageFid = $nodeImage[0]['target_id'];
      }

      // Use the default banner as a fallback.
      else {
        if (($config = $this->config->get('default')) && isset($config['banner_image'][0])) {
          $imageFid = $config['banner_image'][0];
        }
      }
    }
    // If the node doesn't have the banner image field, retrieve the default
    // banner image.
    else {
      if (($config = $this->config->get('default')) && isset($config['banner_image'][0])) {
        $imageFid = $config['banner_image'][0];
      }
    }

    // If there is a banner image.
    if ($imageFid && ($file = $this->fileStorage->load($imageFid))) {

      // Validate the image.
      $fileUri = $file->getFileUri();
      $image = $this->imageFactory->get($fileUri);

      if ($image->isValid()) {
        $build += [
          '#banner_style' => 'banner_image',
          '#banner_url' => $this->imageStyleStorage->load('banner_image')->buildUrl($fileUri),
        ];
      }
    }
    $build += ['#select' => $this->formBuilder->getForm(ChapterSelectForm::class)];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeTags(parent::getCacheContexts(), ['url']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['minfin:bannerImage']);
  }

}
