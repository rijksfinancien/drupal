<?php

declare(strict_types = 1);

namespace Drupal\minfin_corona_visuals\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\minfin\MinfinSourceFileServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CoronaVisualBaseBlock' Block.
 */
abstract class CoronaVisualBaseBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The minfin source file service.
   *
   * @var \Drupal\minfin\MinfinSourceFileServiceInterface
   */
  protected $minfinSourceFileService;

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
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, MinfinSourceFileServiceInterface $minfinSourceFileService) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->minfinSourceFileService = $minfinSourceFileService;
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

}
