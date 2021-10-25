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
 * Base controller for component pages.
 */
abstract class ComponentBaseController extends KamerstukController {

  /**
   * Retrieve the name of the kamerstuk.
   *
   * @param string $phase
   *   The phase.
   *
   * @return string
   *   The name.
   */
  public function getName(string $phase = ''): string {
    if ($phase === 'OWB') {
      return 'Begroting';
    }
    if ($phase === 'JV') {
      return 'Slotwet';
    }
    if ($phase === '1SUPP' || $phase === 'O1') {
      return '1e suppletoire';
    }
    if ($phase === '2SUPP' || $phase === 'O2') {
      return '2e suppletoire';
    }
    if ($phase === 'ISB') {
      return 'Incidentele Suppletoire Begrotingen';
    }

    return '';
  }

  /**
   * Renders the chapter overview page.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   *
   * @return array
   *   The render array.
   */
  public function buildOverview(int $year, string $phase): array {
    $type = $this->getType();
    $hoofdstukken = [];
    foreach ($this->getBuildOverviewData($type, $year, $phase) as $hoofdstukMinfinId => $naam) {
      $url = Url::fromRoute('minfin.' . $type . '.table_of_contents', [
        'year' => $year,
        'phase' => $phase,
        'hoofdstukMinfinId' => $hoofdstukMinfinId,
      ]);

      $hoofdstukken[$hoofdstukMinfinId]['title'] = $naam;
      if ($url->access()) {
        $hoofdstukken[$hoofdstukMinfinId]['url'] = $url;
      }
    }

    return [
      '#theme' => 'minfin_chapter_list',
      '#items' => $hoofdstukken,
      '#prefix_text' => $this->getKamerstukkenOverviewText($type . '_' . $phase, 'prefix', ['[year]' => $year]),
      '#suffix_text' => $this->getKamerstukkenOverviewText($type . '_' . $phase, 'suffix', ['[year]' => $year]),
    ];
  }

  /**
   * Renders the chapter page.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   The render array.
   */
  public function buildTableOfContents(int $year, string $phase, string $hoofdstukMinfinId, bool $appendix = FALSE): array {
    $type = $this->getType();

    $bijlagen = [];
    if ($appendix) {
      $data = $this->buildTableOfContentsData($year, $phase, $hoofdstukMinfinId, TRUE);
      $items = $this->getTableOfContentsKammerstukken($data, 'minfin.' . $type . '.appendix.anchor', [
        'year' => $year,
        'phase' => $phase,
        'hoofdstukMinfinId' => $hoofdstukMinfinId,
      ]);
    }
    else {
      $data = $this->buildTableOfContentsData($year, $phase, $hoofdstukMinfinId);
      $children = $this->getTableOfContentsKammerstukken($data, 'minfin.' . $type . '.anchor', [
        'year' => $year,
        'phase' => $phase,
        'hoofdstukMinfinId' => $hoofdstukMinfinId,
      ]);

      if ($data = $this->buildTableOfContentsData($year, $phase, $hoofdstukMinfinId, TRUE)) {
        $bijlagen = $this->getTableOfContentsKammerstukken($data, 'minfin.' . $type . '.appendix.anchor', [
          'year' => $year,
          'phase' => $phase,
          'hoofdstukMinfinId' => $hoofdstukMinfinId,
        ]);
        array_unshift($bijlagen, [
          'value' => Link::createFromRoute(strtoupper('Bijlagen bij ' . $this->getName()), 'minfin.' . $type . '.appendix', [
            'year' => $year,
          ]),
        ]);
      }

      $items = $children;
      if ($type === 'memorie_van_toelichting') {
        $url = Url::fromRoute('minfin.memorie_van_toelichting.voorstel_van_wet', [
          'year' => $year,
          'phase' => $phase,
          'hoofdstukMinfinId' => $hoofdstukMinfinId,
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
      }
      if ($type === 'isb_memorie_van_toelichting') {
        $url = Url::fromRoute('minfin.isb_memorie_van_toelichting.voorstel_van_wet', [
          'year' => $year,
          'phase' => $phase,
          'hoofdstukMinfinId' => $hoofdstukMinfinId,
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
      }
    }

    if (!$items) {
      throw new NotFoundHttpException();
    }

    $hoofdstukName = $this->getChapterTitle($year, $hoofdstukMinfinId, FALSE);
    $build['related'] = [
      '#theme' => 'minfin_related',
      '#dossiers' => $this->getTableOfContentsDossiers($type, $year, $phase, $hoofdstukMinfinId),
      '#information' => $this->getTableOfContentsInformation($year, $hoofdstukMinfinId, $hoofdstukName),
      '#beleidsevaluatie_link' => $this->getBeleidsevaluatieLink($year, $hoofdstukMinfinId),
      '#performance_information_links' => $this->getArtikelLinks('performance information', $hoofdstukMinfinId),
      '#policy_review_links' => $this->getArtikelLinks('policy review', $hoofdstukMinfinId),
      '#cbs_links' => $this->getArtikelLinks('cbs', $hoofdstukMinfinId),
      '#pdfs' => $this->getKamerstukPdfLinks($type, $year, $phase, $hoofdstukMinfinId),
      '#appendixes' => $this->getKamerstukAppendixesLinks($appendix, $type, $year, $phase, $hoofdstukMinfinId),
      '#visual' => $this->getVisualLink($hoofdstukName ?? $this->t('Visual'), $year, $phase, $hoofdstukMinfinId),
    ];

    $build['toc'] = [
      '#theme' => 'minfin_toc',
      '#items' => $items,
      '#appendix' => $bijlagen,
    ];

    return $build;
  }

  /**
   * Renders the anchor page.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $anchor
   *   The anchor.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   The render array.
   */
  public function buildAnchorPage(int $year, string $phase, string $hoofdstukMinfinId, string $anchor, bool $appendix = FALSE): array {
    $kamerstuk = $this->buildAnchorData($year, $phase, $hoofdstukMinfinId, $anchor, $appendix);
    $type = $this->getType();
    if ($html = $kamerstuk['html'] ?? NULL) {
      $html = Xss::filterAdmin($html);
      $html = $this->addTableWrappers($html);
      $html = $this->clean($html);

      $navigationLinksRenderArray = [
        $this->buildNavigationLinks($year, $phase, $hoofdstukMinfinId, $anchor, $appendix),
      ];

      if ($kamerstuk['artikel_minfin_id'] ?? FALSE) {
        $artikelMinfinId = $kamerstuk['artikel_minfin_id'];
        $hoofdstukName = $this->getChapterTitle($year, $hoofdstukMinfinId, FALSE);
        $artikelName = $this->connection->select('mf_artikel', 'a')
          ->fields('a', ['naam'])
          ->condition('a.jaar', $year)
          ->condition('a.hoofdstuk_minfin_id', $kamerstuk['hoofdstuk_alternatief_id'] ?? $hoofdstukMinfinId)
          ->condition('a.artikel_minfin_id', $artikelMinfinId)
          ->execute()->fetchField();

        $related = [
          '#theme' => 'minfin_related_anchor',
          '#budget_table_id' => (int) $kamerstuk['b_tabel'] ?? 0,
          '#information' => $this->getTableOfContentsInformation($year, $hoofdstukMinfinId, $hoofdstukName),
          '#beleidsevaluatie_link' => $this->getBeleidsevaluatieLink($year, $hoofdstukMinfinId, $artikelMinfinId),
          '#performance_information_links' => $this->getArtikelLinks('performance information', $hoofdstukMinfinId, $artikelMinfinId),
          '#policy_review_links' => $this->getArtikelLinks('policy review', $hoofdstukMinfinId, $artikelMinfinId),
          '#cbs_links' => $this->getArtikelLinks('cbs', $hoofdstukMinfinId, $artikelMinfinId),
          '#visual' => $this->getVisualLink($artikelName ?? $this->t('Visual'), $year, $phase, $kamerstuk['hoofdstuk_alternatief_id'] ?? $hoofdstukMinfinId, $artikelMinfinId),
        ];
      }

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
        'related' => $related ?? [],
        // @todo Add cache tag for importer.
        'kamerstuk' => [
          '#theme' => 'kamerstuk',
          '#html' => $html,
          '#title' => $kamerstuk['naam'] ?? '',
          '#keywords' => 'Uitgaven, ' . $phase . ', ' . $year,
          '#cache' => [
            'max-age' => 0,
          ],
        ],
        'navigation_bottom' => $navigationLinksRenderArray,
        'notes' => $this->buildNotes($type, $year, $phase, $hoofdstukMinfinId ?? NULL, ($kamerstuk['naam'] . $html)),
      ];
    }
    return [];
  }

  /**
   * Retrieve the data to build the table of contents page.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   The table of contents page data.
   */
  protected function buildTableOfContentsData(int $year, string $phase, string $hoofdstukMinfinId, bool $appendix = FALSE): array {
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
    $query->condition('kamerstuk.fase', $phase);
    $query->condition('kamerstuk.hoofdstuk_minfin_id', $hoofdstukMinfinId);
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
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $anchor
   *   The anchor.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   The anchor page data.
   */
  protected function buildAnchorData(int $year, string $phase, string $hoofdstukMinfinId, string $anchor, bool $appendix = FALSE): array {
    $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
    $query = $this->connection->select($table, 'kamerstuk');
    $query->fields('kamerstuk');
    $query->condition('kamerstuk.jaar', $year);
    $query->condition('kamerstuk.type', $this->getDatabaseType());
    $query->condition('kamerstuk.fase', $phase);
    $query->condition('kamerstuk.hoofdstuk_minfin_id', $hoofdstukMinfinId);
    $query->condition('kamerstuk.anchor', $anchor);
    if ($data = $query->execute()->fetchAssoc()) {
      return $data;
    }
    return [];
  }

  /**
   * Validates access to the overview page.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function buildOverviewAccess(int $year, string $phase): AccessResultInterface {
    $query = $this->connection->select('mf_kamerstuk', 'k');
    $query->condition('k.type', $this->getType(), '=');
    $query->condition('k.jaar', $year, '=');
    $query->condition('k.fase', $phase, '=');
    if ((bool) $query->countQuery()->execute()->fetchField()) {
      return new AccessResultAllowed();
    }
    return new AccessResultForbidden();
  }

  /**
   * Validates access to the table of contents page.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function buildTableOfContentsAccess(int $year, string $phase, string $hoofdstukMinfinId, bool $appendix = FALSE): AccessResultInterface {
    if ($this->buildTableOfContentsData($year, $phase, $hoofdstukMinfinId, $appendix)) {
      return new AccessResultAllowed();
    }
    return new AccessResultForbidden();
  }

  /**
   * Validates access to the anchor page.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $anchor
   *   The anchor.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function buildAnchorPageAccess(int $year, string $phase, string $hoofdstukMinfinId, string $anchor): AccessResultInterface {
    if ($kamerstuk = $this->buildAnchorData($year, $phase, $hoofdstukMinfinId, $anchor)) {
      if (!$kamerstuk['empty_record']) {
        return new AccessResultAllowed();
      }
    }
    return new AccessResultForbidden();
  }

}
