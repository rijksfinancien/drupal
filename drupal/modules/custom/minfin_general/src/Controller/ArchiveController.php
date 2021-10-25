<?php

namespace Drupal\minfin_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\minfin\MinfinNamingServiceInterface;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates the archive page.
 */
class ArchiveController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  public $connection;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * The minfin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingServiceInterface
   */
  protected $minfinNamingService;

  /**
   * AllController constructor.
   *
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNamingService
   *   The minfin naming service.
   */
  public function __construct(MinfinServiceInterface $minfinService, MinfinNamingServiceInterface $minfinNamingService) {
    $this->minfinService = $minfinService;
    $this->minfinNamingService = $minfinNamingService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('minfin.minfin'),
      $container->get('minfin.naming')
    );
  }

  /**
   * Get the title for the page.
   *
   * @param null|string $from
   *   The from year.
   * @param null|string $till
   *   The till year.
   *
   * @return string
   *   The title.
   */
  public function title(?string $from, ?string $till): string {
    $fromYear = (int) $from;
    $tillYear = (int) $till;
    $minYear = $this->minfinService->getFirstYear();
    if ($fromYear >= $minYear && $tillYear >= $minYear && $tillYear <= $this->minfinService->getLastYear()) {
      if ($fromYear === $tillYear) {
        return $this->t('Archive @year', ['@year' => $fromYear]);
      }

      return $this->t('Archive @min till @max', ['@min' => min($fromYear, $tillYear), '@max' => max($fromYear, $tillYear)]);
    }

    return $this->t('Archive');
  }

  /**
   * Build the actual page.
   *
   * @param null|string $from
   *   The from year.
   * @param null|string $till
   *   The till year.
   * @param null|string $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content(?string $from, ?string $till, ?string $hoofdstukMinfinId): array {
    $fromYear = (int) $from;
    $tillYear = (int) $till;
    $minYear = $this->minfinService->getFirstYear();
    if ($fromYear < $minYear || $tillYear < $minYear || $tillYear > $this->minfinService->getLastYear()) {
      $tillYear = $this->minfinService->getLastYear();
      $fromYear = $tillYear - 4;
    }

    $years = range($fromYear, $tillYear);
    arsort($years);

    $items = [];
    foreach ($this->minfinService->getCategories() as $category => $links) {
      $categoryDataAvailable = FALSE;
      // Create category.
      $items[$category] = [
        'title' => $category,
        'subcategories' => [],
      ];

      foreach ($links as $link) {
        if (isset($link['text'], $link['routePrefix'])) {
          $subCategoryDataAvailable = FALSE;
          // Create subcategory.
          $subcategory = $link['text'];
          $items[$category]['subcategories'][$subcategory] = [
            'title' => $subcategory,
            'items' => [],
          ];

          foreach ($years as $year) {
            // Create links within subcategory.
            $title = $link['text'] . ' ' . $year;
            $url = $this->minfinService->buildKamerstukUrl($link['routePrefix'], $year, ['#attributes' => ['class' => ['title-link']]], $link['params']['phase'] ?? NULL, $hoofdstukMinfinId);
            $pdfUrl = $this->minfinService->buildKamerstuPdfkUrl($link['routePrefix'], $year, ['#attributes' => ['class' => ['pdf-link']]], $link['params']['phase'] ?? NULL, $hoofdstukMinfinId);

            $urlAccess = $url && $url->access();
            if ($urlAccess || $pdfUrl) {
              $items[$category]['subcategories'][$subcategory]['items'][] = [
                'title' => $urlAccess ? Link::fromTextAndUrl($title, $url)->toRenderable() : $title,
                'pdf' => $pdfUrl ? Link::fromTextAndUrl($this->t('PDF'), $pdfUrl)->toRenderable() : NULL,
              ];
              $categoryDataAvailable = TRUE;
              $subCategoryDataAvailable = TRUE;
            }

            else {
              $items[$category]['subcategories'][$subcategory]['items'][] = [
                'title' => [
                  '#type' => 'html_tag',
                  '#tag' => 'span',
                  '#value' => $title,
                  '#attributes' => [
                    'title' => $this->t('Part is not available for this period'),
                    'class' => ['not-available'],
                  ],
                ],
              ];
            }
          }

          if (!$subCategoryDataAvailable) {
            unset($items[$category]['subcategories'][$subcategory]['items']);
          }
        }
      }

      if (!$categoryDataAvailable) {
        unset($items[$category]['subcategories']);
      }
    }

    return [
      '#theme' => 'minfin_archive',
      '#items' => $items,
    ];
  }

}
