<?php

namespace Drupal\minfin_kamerstuk\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Base controller for kamerstuk pages.
 */
abstract class NotaBaseController extends KamerstukController {

  /**
   * Retrieve the phase of the kamerstuk.
   *
   * @return string
   *   The phase.
   */
  abstract protected function getPhase(): string;

  /**
   * Builds and returns the renderable array for the table of contents page.
   *
   * @param int $year
   *   The year.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   A renderable array representing the content of the page.
   */
  public function buildTableOfContents(int $year, bool $appendix = FALSE): array {
    $phase = $this->getPhase();
    $type = $this->getType();
    $name = $this->getName();

    $bijlagen = [];
    if ($appendix) {
      $data = $this->buildTableOfContentsData($year, TRUE);
      $items = $this->getTableOfContentsKammerstukken($data, 'minfin.' . $type . '.appendix.anchor', [
        'year' => $year,
      ]);
    }
    else {
      $data = $this->buildTableOfContentsData($year);
      $children = $this->getTableOfContentsKammerstukken($data, 'minfin.' . $type . '.anchor', [
        'year' => $year,
      ]);

      if ($data = $this->buildTableOfContentsData($year, TRUE)) {
        $bijlagen = $this->getTableOfContentsKammerstukken($data, 'minfin.' . $type . '.appendix.anchor', [
          'year' => $year,
        ]);
        array_unshift($bijlagen, [
          'value' => Link::createFromRoute(strtoupper('Bijlagen bij ' . $name), 'minfin.' . $type . '.appendix', [
            'year' => $year,
          ]),
        ]);
      }

      switch ($type) {
        case 'belastingplan_memorie_van_toelichting':
          $url = Url::fromRoute('minfin.belastingplan_voorstel_van_wet.table_of_contents', [
            'year' => $year,
          ]);

          $items = [
            [
              'value' => ($url->access()) ? new Link('Voorstel van wet', $url) : 'Voorstel van wet',
            ],
            [
              'value' => 'Memorie van toelichting',
              'children' => $children,
            ],
          ];
          break;

        case 'belastingplan_voorstel_van_wet':
          $url = Url::fromRoute('minfin.belastingplan_memorie_van_toelichting.table_of_contents', [
            'year' => $year,
          ]);

          $items = [
            [
              'value' => ($url->access()) ? new Link('Memorie van toelichting', $url) : 'Voorstel van wet',
            ],
            [
              'value' => 'Voorstel van wet',
              'children' => $children,
            ],
          ];
          break;

        default:
          $items = $children;
          break;
      }
    }

    if (!$items) {
      throw new NotFoundHttpException();
    }

    $information = [];
    $previousYear = $year - 1;
    $url = Url::fromRoute('minfin.' . $type . '.table_of_contents', ['year' => $previousYear]);
    if ($url->access()) {
      $information[] = Link::fromTextAndUrl($name . ' - ' . $previousYear, $url);
    }

    $build['related'] = [
      '#theme' => 'minfin_related',
      '#dossiers' => $this->getTableOfContentsDossiers($type, $year, $phase),
      '#information' => $information,
      '#pdfs' => $this->getKamerstukPdfLinks($type, $year, $phase),
      '#appendixes' => $this->getKamerstukAppendixesLinks($appendix, $type, $year, $phase),
    ];

    $build['toc'] = [
      '#theme' => 'minfin_toc',
      '#items' => $items,
      '#appendix' => $bijlagen,
    ];

    return $build;
  }

  /**
   * Builds and returns the renderable array for the anchor page.
   *
   * @param int $year
   *   The year.
   * @param string $anchor
   *   The anchor.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   A renderable array representing the content of the page.
   */
  public function buildAnchorPage(int $year, string $anchor, bool $appendix = FALSE): array {
    $kamerstuk = $this->buildAnchorData($year, $anchor, $appendix);
    if ($html = $kamerstuk['html'] ?? NULL) {
      $html = Xss::filterAdmin($html);
      $html = $this->addTableWrappers($html);
      $html = $this->clean($html);

      $navigationLinksRenderArray = [
        $this->buildNavigationLinks($year, $this->getPhase(), $kamerstuk['hoofdstuk_minfin_id'], $anchor, $appendix),
      ];

      return [
        '#title' => $kamerstuk['naam'] ?? NULL,
        'navigation_top' => $navigationLinksRenderArray,
        'title' => [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#value' => $kamerstuk['naam'] ?? NULL,
          '#attributes' => [
            'class' => ['limit-width'],
          ],
        ],
        // @todo Add cache tag for importer.
        'kamerstuk' => [
          '#theme' => 'kamerstuk',
          '#html' => $html,
          '#title' => $kamerstuk['naam'] ?? '',
          '#keywords' => 'Nota, ' . $anchor . ', ' . $year,
          '#cache' => [
            'max-age' => 0,
          ],
        ],
        'navigation_bottom' => $navigationLinksRenderArray,
        'notes' => $this->buildNotes($this->getType(), $year, $this->getPhase(), $kamerstuk['hoofdstuk_minfin_id'] ?? NULL, $kamerstuk['naam'] . $html),
      ];
    }
    return [];
  }

  /**
   * Retrieve the data to build the table of contents page.
   *
   * @param int $year
   *   The year.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   The table of contents page data.
   */
  protected function buildTableOfContentsData(int $year, bool $appendix = FALSE): array {
    $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
    $query = $this->connection->select($table, 'kamerstuk');
    $query->fields('kamerstuk', [
      'level_1',
      'level_2',
      'level_3',
      'anchor',
      'naam',
      'empty_record',
    ]);
    $query->condition('kamerstuk.jaar', $year);
    $query->condition('kamerstuk.type', $this->getType());
    if ($result = $query->execute()) {
      return $result->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    }
    return [];
  }

  /**
   * Retrieve the data to build the table of contents page.
   *
   * @param int $year
   *   The year.
   * @param string $anchor
   *   The anchor.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   The anchor page data.
   */
  protected function buildAnchorData(int $year, string $anchor, bool $appendix = FALSE): array {
    $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
    $query = $this->connection->select($table, 'kamerstuk');
    $query->fields('kamerstuk');
    $query->condition('kamerstuk.jaar', $year);
    $query->condition('kamerstuk.type', $this->getType());
    $query->condition('kamerstuk.fase', $this->getPhase());
    $query->condition('kamerstuk.anchor', $anchor);
    if ($data = $query->execute()->fetchAssoc()) {
      return $data;
    }
    return [];
  }

  /**
   * Validates access to the table of contents page.
   *
   * @param int $year
   *   The year.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function buildTableOfContentsAccess(int $year, bool $appendix = FALSE): AccessResultInterface {
    if ($this->buildTableOfContentsData($year, $appendix)) {
      return new AccessResultAllowed();
    }
    return new AccessResultForbidden();
  }

  /**
   * Validates access to the anchor page.
   *
   * @param int $year
   *   The year.
   * @param string $anchor
   *   The anchor.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function buildAnchorPageAccess(int $year, string $anchor, bool $appendix = FALSE): AccessResultInterface {
    if ($kamerstuk = $this->buildAnchorData($year, $anchor, $appendix)) {
      if (!$kamerstuk['empty_record']) {
        return new AccessResultAllowed();
      }
    }
    return new AccessResultForbidden();
  }

}
