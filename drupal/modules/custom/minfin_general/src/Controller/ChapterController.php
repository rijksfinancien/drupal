<?php

namespace Drupal\minfin_general\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\minfin\MinfinNamingServiceInterface;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Creates the chapter pages.
 */
class ChapterController extends ControllerBase {

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
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Node view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $nodeViewBuilder;

  /**
   * The minfin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingServiceInterface
   */
  protected $minfinNamingService;

  /**
   * ChapterRoutes constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNamingService
   *   The minfin naming service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, MinfinServiceInterface $minfinService, MinfinNamingServiceInterface $minfinNamingService) {
    $this->minfinService = $minfinService;
    $this->config = $configFactory->get('minfin_general.chapter_page.settings');
    $this->nodeStorage = $this->entityTypeManager()->getStorage('node');
    $this->nodeViewBuilder = $this->entityTypeManager()->getViewBuilder('node');
    $this->minfinNamingService = $minfinNamingService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('minfin.minfin'),
      $container->get('minfin.naming')
    );
  }

  /**
   * Get the title for the page.
   *
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   *
   * @return string
   *   The title.
   */
  public function title(string $hoofdstukMinfinId): string {
    $year = $this->minfinService->getActiveYear();
    return $this->minfinNamingService->getHoofdstukName($year, $hoofdstukMinfinId, TRUE);
  }

  /**
   * Build the actual page.
   *
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content(string $hoofdstukMinfinId): array {
    $year = $this->minfinService->getActiveYear();
    $name = $this->minfinNamingService->getHoofdstukName($year, $hoofdstukMinfinId, TRUE);

    if (!$name) {
      throw new NotFoundHttpException();
    }

    // Get global content from the chapter node.
    $build = [];
    if ($nid = $this->config->get('chapter_node')) {
      $node = $this->nodeStorage->load($nid);
      $build = $this->nodeViewBuilder->view($node);
    }

    return [
      [
        '#theme' => 'chapter_page',
        '#hoofdstuk_naam' => $name,
        '#hoofdstuk_minfin_id' => $hoofdstukMinfinId,
        '#jaar' => $year,
      ],
      $build,
    ];
  }

}
