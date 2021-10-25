<?php

namespace Drupal\minfin_visuals\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\minfin\MinfinSourceFileServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class VisualController extends ControllerBase {

  /**
   * Needed for csv download.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * The minfin source file service.
   *
   * @var \Drupal\minfin\MinfinSourceFileServiceInterface
   */
  protected $minfinSourceFileService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('minfin.source_file'),
    );
  }

  /**
   * VisualController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\minfin\MinfinSourceFileServiceInterface $minfinSourceFileService
   *   The minfin source file service.
   */
  public function __construct(RequestStack $requestStack, MinfinSourceFileServiceInterface $minfinSourceFileService) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->minfinSourceFileService = $minfinSourceFileService;
  }

  /**
   * Builds the visual page.
   *
   * @param int $jaar
   *   The year.
   * @param string $fase
   *   The phase.
   *
   * @return array
   *   A Drupal render array.
   */
  public function build(int $jaar, string $fase): array {
    $sourceUrl = NULL;
    $renamePhases = [
      'begroting' => 'owb',
      'suppletoire1' => 'o1',
      'suppletoire2' => 'o2',
      'jaarverslag' => 'jv',
    ];
    $renamedPhase = $renamePhases[$fase] ?? $fase;
    if ($sourceFile = $this->minfinSourceFileService->getLastSourceFile('budgettaire_tabellen', $renamedPhase, $jaar)) {
      $sourceUrl = file_create_url($sourceFile->getFileUri());
    }

    $replace = [
      'visuals' => 'budgettaire_tabellen',
      'jaarverslag' => 'JV',
      'uitgaven' => 'U',
      'begroting' => 'OWB',
      'ontvangsten' => 'O',
      'verplichtingen' => 'V',
      'corona' => 'C',
    ];
    $apiUrl = '/csv' . str_replace(array_keys($replace), array_values($replace), $this->currentRequest->getRequestUri());

    return [
      '#theme' => 'visuals',
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => ['minfin_visuals/visuals'],
        'drupalSettings' => [
          'minfin' => [
            'visual_download' => [
              'api' => $apiUrl,
              'source' => $sourceUrl,
            ],
          ],
        ],
      ],
    ];
  }

}
