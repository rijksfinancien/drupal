<?php

declare(strict_types = 1);

namespace Drupal\minfin_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\minfin\MinfinSourceFileServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'VisualBaseBlock' Block.
 */
abstract class VisualBaseBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The minfin source file service.
   *
   * @var \Drupal\minfin\MinfinSourceFileServiceInterface
   */
  protected $minfinSourceFileService;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * LeningenVisualBlock constructor.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $pluginId
   *   The block id.
   * @param mixed $pluginDefinition
   *   The block definition.
   * @param \Drupal\minfin\MinfinSourceFileServiceInterface $minfinSourceFileService
   *   The minfin source file service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, MinfinSourceFileServiceInterface $minfinSourceFileService, RequestStack $requestStack) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->minfinSourceFileService = $minfinSourceFileService;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('minfin.source_file'),
      $container->get('request_stack'),
    );
  }

  /**
   * Get the last source file for the given values.
   *
   * @param string $importType
   *   The import type.
   * @param string|null $subType
   *   The sub type.
   * @param int|null $year
   *   The year.
   *
   * @return string|null
   *   The source file or null if not found.
   */
  protected function getSourceFile(string $importType, ?string $subType = NULL, ?int $year = NULL): ?string {
    $sourceUrl = NULL;
    if ($sourceFile = $this->minfinSourceFileService->getLastSourceFile($importType, $subType)) {
      $sourceUrl = file_create_url($sourceFile->getFileUri());
    }

    return $sourceUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path', 'languages']);
  }

}
