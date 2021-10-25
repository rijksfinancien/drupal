<?php

namespace Drupal\minfin_kamerstuk\Controller;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\minfin\MinfinNamingServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base controller for kamerstuk pages.
 */
abstract class KamerstukController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The minfin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingServiceInterface
   */
  protected $minfinNamingService;

  /**
   * Constructs a KamerstukController object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNamingService
   *   The minfin naming service.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entityTypeManager, MinfinNamingServiceInterface $minfinNamingService) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->minfinNamingService = $minfinNamingService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('minfin.naming')
    );
  }

  /**
   * Retrieve the type of the kamerstuk.
   *
   * @return string
   *   The type.
   */
  abstract protected function getType(): string;

  /**
   * Retrieve the database type of kamerstuk.
   *
   * In most cases this is the same as getType(), but in some cases its not.
   * This mainly applies to VvW. In most cases the VvW pretends to be the MvT,
   * but in other cases its still needs to be its own type.
   *
   * @return string
   *   The real type.
   */
  protected function getDatabaseType(): string {
    return $this->getType();
  }

  /**
   * Retrieve the name of the kamerstuk.
   *
   * @return string
   *   The name.
   */
  abstract protected function getName(): string;

  /**
   * Get the title for a chapter page.
   *
   * @param string $year
   *   The year.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param bool $prefix
   *   Does the name have to be prefixed with the $hoofdstukMinfinId.
   *
   * @return string
   *   The chapter title.
   */
  public function getChapterTitle(string $year, string $hoofdstukMinfinId, bool $prefix = TRUE): string {
    $query = $this->connection->select('mf_hoofdstuk', 'hoofdstuk');
    $query->fields('hoofdstuk', ['naam']);
    $query->condition('hoofdstuk.jaar', $year);
    $query->condition('hoofdstuk.hoofdstuk_minfin_id', $hoofdstukMinfinId);
    if ($name = $query->execute()->fetchField()) {
      return ($prefix ? $hoofdstukMinfinId . ' ' : '') . $name;
    }
    return '';
  }

  /**
   * Helper functionm to add table wrapper for given HTML.
   *
   * @param string $html
   *   The HTML.
   *
   * @return string
   *   The formatted HTML.
   */
  protected function addTableWrappers(string $html): string {
    return preg_replace_callback('~<table.*?</table>~is', static function ($match) {
      $functionalTable = [
        '#theme' => 'functional-table',
        '#table' => $match[0],
      ];
      return render($functionalTable);

    }, $html);
  }

  /**
   * Helper function to clean imported HTML.
   *
   * @param string $html
   *   The HTML.
   *
   * @return string
   *   The cleaned HTML.
   */
  protected function clean(string $html): string {
    $html = preg_replace('#<span class=\"ol\">(.*?)</span>#is', '', $html);
    $html = str_replace('href="kst', 'href="https://zoek.officielebekendmakingen.nl/kst', $html);
    $html = preg_replace('#<h5 class=\"note-close\">(.*?)</h5>#is', '', $html);
    return $html;
  }

  /**
   * Render the navigation links.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param null|string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $anchor
   *   The anchor.
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   *
   * @return array
   *   The render array.
   */
  protected function buildNavigationLinks(int $year, string $phase, ?string $hoofdstukMinfinId, string $anchor, bool $appendix = FALSE): array {
    $type = $this->getType();
    $build['#theme'] = 'kamerstuk_navigatie';

    $values = [];
    $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
    $query = $this->connection->select($table, 'kamerstuk');
    $query->fields('kamerstuk', ['level_1', 'level_2', 'level_3', 'anchor']);
    $query->condition('type', $this->getDatabaseType());
    $query->condition('jaar', $year);
    $query->condition('fase', $phase);
    if ($hoofdstukMinfinId) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    }
    else {
      $query->isNull('hoofdstuk_minfin_id');
    }

    // This will filter out the "not autorized" pages from the navigaion links.
    $query->condition('empty_record', 0, '=');

    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $level = (int) $record['level_1'] . '.' . (int) $record['level_2'] . '.' . (int) $record['level_3'];
      $values[$level] = $record['anchor'];
    }

    // Sort the array keys with the php natsort() function and restructure the
    // original array the sorted array.
    $list = [];
    $keys = array_keys($values);
    natsort($keys);
    foreach ($keys as $k) {
      $list[$k] = $values[$k];
    }

    // Set the pointer in the array to the current anchor. This way we can use
    // the prev() and next() functions to create the correct navigation links.
    if (in_array($anchor, $list, TRUE)) {
      while (current($list) !== $anchor) {
        next($list);
      }
    }

    $routeParams = [
      'year' => $year,
    ];

    // Phase.
    $types = [
      'memorie_van_toelichting',
      'voorstel_van_wet',
      'isb_memorie_van_toelichting',
      'isb_voorstel_van_wet',
    ];
    if (in_array($type, $types, TRUE)) {
      $routeParams['phase'] = $phase;
    }

    // Hoofdstuk minfin id.
    $types = [
      'memorie_van_toelichting',
      'voorstel_van_wet',
      'isb_memorie_van_toelichting',
      'isb_voorstel_van_wet',
      'jaarverslag',
    ];
    if (in_array($type, $types, TRUE)) {
      $routeParams['hoofdstukMinfinId'] = $hoofdstukMinfinId;
    }

    $tmp = $list;
    $build['#prev'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#attributes' => ['class' => ['arrow-back-icon']],
      '#value' => $this->t('Previous'),
    ];

    if ($anchor = prev($tmp)) {
      $prevLink = Url::fromRoute('minfin.' . $type . ($appendix ? '.appendix' : '') . '.anchor', array_merge($routeParams, ['anchor' => $anchor]), ['attributes' => ['class' => ['arrow-back-icon']]]);
      if ($prevLink->access()) {
        $build['#prev'] = Link::fromTextAndUrl($this->t('Previous'), $prevLink)->toString();
      }
    }

    $tocLink = Url::fromRoute('minfin.' . $type . ($appendix ? '.appendix' : '.table_of_contents'), $routeParams, [
      'attributes' => [
        'class' => [
          'arrow-up-icon',
          'absolute-icon',
        ],
      ],
    ]);

    if ($tocLink->access()) {
      $build['#toc'] = Link::fromTextAndUrl($this->t('Table of contents'), $tocLink)->toString();
    }

    $tmp = $list;
    $build['#next'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#attributes' => ['class' => ['arrow-forward-icon']],
      '#value' => $this->t('Next'),
    ];

    if ($anchor = next($tmp)) {
      $nextLink = Url::fromRoute('minfin.' . $type . ($appendix ? '.appendix' : '') . '.anchor', array_merge($routeParams, ['anchor' => $anchor]), ['attributes' => ['class' => ['arrow-forward-icon']]]);

      if ($nextLink->access()) {
        $build['#next'] = Link::fromTextAndUrl($this->t('Next'), $nextLink)->toString();
      }
    }

    return $build;
  }

  /**
   * Retrieve the data to build the overview page.
   *
   * @param string $type
   *   The kamerstuk type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   *
   * @return array
   *   The overview page data.
   */
  protected function getBuildOverviewData(string $type, int $year, string $phase): array {
    $query = $this->connection->select('mf_kamerstuk', 'k');
    $query->leftJoin('mf_hoofdstuk', 'h', 'h.hoofdstuk_minfin_id = k.hoofdstuk_minfin_id AND h.jaar = k.jaar');
    $query->fields('k', ['hoofdstuk_minfin_id']);
    $query->fields('h', ['naam']);
    $query->distinct(TRUE);
    $query->condition('k.type', $type, '=');
    $query->condition('k.jaar', $year, '=');
    $query->condition('k.fase', $phase, '=');
    $data = $query->execute()->fetchAllKeyed();

    $sort = $this->config('minfin.chapter_sorting')->get('chapters') ?? [];
    uksort($data, static function ($key1, $key2) use ($sort) {
      return ($sort[$key1] > $sort[$key2] ? 1 : -1);
    });

    return $data;
  }

  /**
   * Get a list with article related links.
   *
   * @param string $category
   *   The category.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   The artikel minfin id.
   *
   * @return array
   *   An array with article related links.
   */
  protected function getArtikelLinks(string $category, string $hoofdstukMinfinId, ?string $artikelMinfinId = NULL): array {
    $links = [];
    $query = $this->connection->select('mf_artikel_link', 'al');
    $query->fields('al', ['link', 'description']);
    if ($artikelMinfinId) {
      $query->condition('al.artikel_minfin_id', $artikelMinfinId, '=');
    }
    else {
      $query->isNull('al.artikel_minfin_id');
    }
    $query->condition('al.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    $query->condition('al.category', $category, '=');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      if (UrlHelper::isValid($record['link'])) {
        $links[] = Link::fromTextAndUrl($record['description'], Url::fromUri($record['link'], ['#attributes' => ['target' => '_blank']]))->toRenderable();
      }
    }

    return $links;
  }

  /**
   * Get beleidsevaluatie link.
   *
   * @param int $year
   *   The year.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   The artikel minfin id.
   *
   * @return \Drupal\Core\Link|null
   *   The beleidsevaluatie link.
   */
  protected function getBeleidsevaluatieLink(int $year, string $hoofdstukMinfinId, ?string $artikelMinfinId = NULL): ?Link {
    $link = NULL;
    try {
      $options = [];

      if ($artikelMinfinId) {
        $artikelNaam = $this->connection->select('mf_artikel', 'a')
          ->fields('a', ['naam'])
          ->condition('a.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
          ->condition('a.artikel_minfin_id', $artikelMinfinId, '=')
          ->condition('a.jaar', $year, '=')
          ->execute()->fetchField();
        if ($artikelNaam) {
          $options = [
            'query' => [
              'artikel_naam' => $artikelNaam,
            ],
          ];
        }
      }
      else {
        $hoofdstukNaam = $this->connection->select('mf_hoofdstuk', 'h')
          ->fields('h', ['naam'])
          ->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
          ->condition('h.jaar', $year, '=')
          ->execute()->fetchField();
        if ($hoofdstukNaam) {
          $options = [
            'query' => [
              'hoofdstuk_naam' => $hoofdstukNaam,
            ],
          ];
        }
      }

      if ($options) {
        $url = Url::fromRoute('minfin_beleidsevaluaties.beleidsonderzoeken', [], $options);
        // This will throw an error if the route doesn't exist.
        $url->toString();
        $link = Link::fromTextAndUrl('Gerelateerde beleidsevaluaties', $url);
      }
    }
    catch (\Exception $e) {
      // Do nothing.
      return NULL;
    }

    return $link;
  }

  /**
   * Get the visual link.
   *
   * @param string $title
   *   The link title.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   The artikel minfin id.
   *
   * @return \Drupal\Core\Link
   *   The visual link.
   */
  protected function getVisualLink(string $title, int $year, string $phase, string $hoofdstukMinfinId, ?string $artikelMinfinId = NULL): Link {
    $routeParams = [
      'jaar' => $year,
      'fase' => 'begroting',
      'vuo' => 'uitgaven',
      'hoofdstukMinfinId' => $hoofdstukMinfinId,
    ];

    if ($artikelMinfinId) {
      $routeParams['artikelMinfinId'] = $artikelMinfinId;
    }

    if ($phase === 'JV') {
      $routeParams['fase'] = 'jaarverslag';
    }
    elseif ($phase === '1SUPP' || $phase === 'O1') {
      $routeParams['fase'] = 'suppletoire1';
    }
    elseif ($phase === '2SUPP' || $phase === 'O2') {
      $routeParams['fase'] = 'suppletoire2';
    }

    $options = [
      'attributes' => [
        'class' => ['visual-link'],
      ],
    ];

    return Link::createFromRoute($title, 'minfin_visuals', $routeParams, $options);
  }

  /**
   * Builds the notes.
   *
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $html
   *   The html.
   *
   * @return array
   *   The notes.
   */
  protected function buildNotes(string $type, int $year, string $phase, ?string $hoofdstukMinfinId, string $html): array {
    $notes = [];
    preg_match_all('/<a[^>]+>/i', $html, $all);
    preg_match_all('/<a href="([^"]*?)"[^>]*>/', $html, $external);
    $internal = array_diff($all[0] ?? [], $external[0] ?? []);
    foreach ($internal as $link) {
      $xml = new \SimpleXMLElement($link . '</a>');
      $href = (string) $xml['href'];
      $minfinVoetstukId = str_replace('#ID-', '', $href);

      // Get voetstuk.
      $query = $this->connection->select('mf_voetstuk');
      $query->fields('mf_voetstuk');
      $query->condition('minfin_voetstuk_id', $minfinVoetstukId);
      $query->condition('type', $type);
      $query->condition('jaar', $year);
      $query->condition('fase', $phase);
      if ($hoofdstukMinfinId) {
        $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId);
      }
      else {
        $query->isNull('hoofdstuk_minfin_id');
      }

      if ($result = $query->execute()) {
        if ($data = $result->fetchAll(\PDO::FETCH_ASSOC)) {
          $voetstuk = reset($data);
          $notes[$voetstuk['voetstuk_id']] = $this->clean($voetstuk['html']);
        }
      }
    }

    return [
      '#theme' => 'minfin_notes',
      '#notes' => $notes,
    ];
  }

  /**
   * Helper function to get the kammerstukken for the buildTableOfContents.
   *
   * @param array $data
   *   The buildTableOfContents data.
   * @param string $routeName
   *   The route name.
   * @param array $additionalRouteParams
   *   Any required additional rroute params.
   *
   * @return array
   *   The kammerstukken.
   */
  protected function getTableOfContentsKammerstukken(array $data, string $routeName, array $additionalRouteParams = []): array {
    $kamerstukken = [];
    foreach ($data as $kamerstuk) {
      $parents = [];
      foreach (['level_1', 'level_2', 'level_3'] as $level) {
        if (isset($kamerstuk[$level]) && $kamerstuk[$level] !== '') {
          if ($parents) {
            $parents[] = 'children';
          }

          $parents[] = $kamerstuk[$level];
          continue;
        }
        break;
      }

      $parents[] = 'value';

      $anchor = ['anchor' => $kamerstuk['anchor'] ?? NULL];
      $url = Url::fromRoute($routeName, array_merge($anchor, $additionalRouteParams));

      // Strip the footnotes from the title here.
      $title = preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $kamerstuk['naam']);
      $title = Xss::filterAdmin($title);
      $value = Markup::create($title);
      if (!$kamerstuk['empty_record']) {
        $value = new Link($value, $url);
      }

      NestedArray::setValue($kamerstukken, $parents, $value);
    }

    return $kamerstukken;
  }

  /**
   * Helper function to get the dossiers for the buildTableOfContents.
   *
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin ID.
   *
   * @return array
   *   The dossiers.
   */
  protected function getTableOfContentsDossiers(string $type, int $year, string $phase, ?string $hoofdstukMinfinId = NULL): array {
    $dossiers = [];
    $code = $this->connection->select('mf_kamerstuk_dossier', 'kd')
      ->fields('kd', ['dossier_number'])
      ->condition('type', $type, '=')
      ->condition('jaar', $year, '=')
      ->condition('fase', $phase, '=')
      ->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
      ->execute()->fetchField();

    if ($code) {
      if ($hoofdstukMinfinId && substr($phase, 0, 3) !== 'ISB') {
        $code .= '-' . $hoofdstukMinfinId;
      }

      /** @var \Drupal\minfin\Entity\DossierInterface $dossier */
      $dossiers[] = Link::fromTextAndUrl($this->t('View dossier'), Url::fromUri('https://zoek.officielebekendmakingen.nl/dossier/' . $code));
      $dossiers[] = Link::fromTextAndUrl($this->t('Motions in dossier'), Url::fromUri('https://zoek.officielebekendmakingen.nl/resultaten', [
        'query' => [
          'q' => '(c.product-area=="officielepublicaties")and(((w.publicatienaam=="Kamerstuk")and(w.dossiernummer=="' . $code . '"))and((w.subrubriek="Motie")))',
          'zv' => '',
          'col' => 'Kamerstuk,Moties',
        ],
      ]));
      $dossiers[] = Link::fromTextAndUrl($this->t("Nota's van wijziging in dossier"), Url::fromUri('https://zoek.officielebekendmakingen.nl/resultaten', [
        'query' => [
          'q' => '(c.product-area=="officielepublicaties")and(((w.publicatienaam=="Kamerstuk")and(w.dossiernummer=="' . $code . '"))and((w.subrubriek="Wijziging")))',
          'zv' => '',
          'col' => 'Kamerstuk,Moties',
        ],
      ]));
      $dossiers[] = Link::fromTextAndUrl('Amendementen in dossier', Url::fromUri('https://zoek.officielebekendmakingen.nl/resultaten', [
        'query' => [
          'q' => '(c.product-area=="officielepublicaties")and(((w.publicatienaam=="Kamerstuk")and(w.dossiernummer=="' . $code . '"))and((w.subrubriek="amendement")))',
          'zv' => '',
          'col' => 'Kamerstuk,Moties',
        ],
      ]));
    }

    return $dossiers;
  }

  /**
   * Helper function to get the information for the buildTableOfContents.
   *
   * @param int $year
   *   The year.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $hoofdstukName
   *   The hoofdstuk name.
   *
   * @return array
   *   The information.
   */
  protected function getTableOfContentsInformation(int $year, string $hoofdstukMinfinId, string $hoofdstukName): array {
    $information = [];
    $previousYear = $year - 1;
    $routes = [
      'begroting' => [
        'route' => 'minfin.memorie_van_toelichting.table_of_contents',
        'text' => 'de Begroting',
        'params' => [
          'year' => $previousYear,
          'phase' => 'OWB',
          'hoofdstukMinfinId' => $hoofdstukMinfinId,
        ],
      ],
      'jaarverslag' => [
        'route' => 'minfin.jaarverslag.table_of_contents',
        'text' => 'het Jaarverslag',
        'params' => [
          'year' => $previousYear,
          'hoofdstukMinfinId' => $hoofdstukMinfinId,
        ],
      ],
    ];

    foreach ($routes as $index => $link) {
      $text = $this->t('@hoofdstuk in @phase @year', [
        '@hoofdstuk' => $hoofdstukName,
        '@phase' => $link['text'],
        '@year' => $previousYear,
      ]);

      $information[$index] = $text;
      $url = Url::fromRoute($link['route'], $link['params'] ?? []);
      if ($url->access()) {
        $information[$index] = Link::fromTextAndUrl($text, $url);
      }
    }

    return $information;
  }

  /**
   * Return the text for the kamerstuk overview page.
   *
   * @param string $key
   *   The config key.
   * @param string $type
   *   Either prefix or suffix.
   * @param array $tokens
   *   An array with tokens for the text.
   *
   * @return array
   *   The render array.
   */
  protected function getKamerstukkenOverviewText(string $key, string $type, array $tokens = []) {
    $config = $this->config('minfin.kamerstuk.text')->get($key);
    if (!empty($config[$type]['value']) && !empty($config[$type]['format'])) {
      return [
        '#type' => 'processed_text',
        '#text' => str_replace(array_keys($tokens), array_values($tokens), $config[$type]['value']),
        '#format' => $config[$type]['format'],
      ];
    }

    return [];
  }

  /**
   * Get the kamerstuk PDF links.
   *
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string|null $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin ID.
   *
   * @return \Drupal\Core\Link[]
   *   An array with links to the PDF's.
   */
  protected function getKamerstukPdfLinks(string $type, int $year, ?string $phase = NULL, ?string $hoofdstukMinfinId = NULL): array {
    $links = [];
    $types = [$type => 'Kamerstuk'];
    if (in_array($type, ['memorie_van_toelichting', 'voorstel_van_wet'], TRUE)) {
      $types = [
        'voorstel_van_wet' => 'Voorstel van wet',
        'memorie_van_toelichting' => 'Memorie van toelichting',
      ];
    }
    elseif (in_array($type, ['belastingplan_memorie_van_toelichting', 'belastingplan_voorstel_van_wet'], TRUE)) {
      $types = [
        'belastingplan_voorstel_van_wet' => 'Voorstel van wet',
        'belastingplan_memorie_van_toelichting' => 'Memorie van toelichting',
      ];
    }
    elseif (in_array($type, ['isb_memorie_van_toelichting', 'isb_voorstel_van_wet'], TRUE)) {
      $types = [
        'isb_voorstel_van_wet' => 'Voorstel van wet',
        'isb_memorie_van_toelichting' => 'Memorie van toelichting',
      ];
    }

    foreach ($types as $newType => $title) {
      $query = $this->connection->select('mf_kamerstuk_files', 'kf');
      $query->fields('kf', ['fid', 'bijlage']);
      $query->condition('type', $newType, '=');
      $query->condition('jaar', $year, '=');
      $query->condition('fase', $phase, '=');
      if ($hoofdstukMinfinId) {
        $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      }
      else {
        $query->isNull('hoofdstuk_minfin_id');
      }
      $query->orderBy('kf.bijlage');

      try {
        $result = $query->execute();
        while ($record = $result->fetchAssoc()) {
          /** @var \Drupal\file\Entity\File $file */
          if ($file = $this->entityTypeManager()->getStorage('file')->load($record['fid'])) {
            $url = Url::fromUri(file_create_url($file->getFileUri()));
            $links[] = Link::fromTextAndUrl($title . ($record['bijlage'] ? ' (Bijlage)' : ''), $url);
          }
        }
      }
      catch (\Exception $e) {
        // Do nothing.
      }
    }
    return $links;
  }

  /**
   * Get the appendix links.
   *
   * @param bool $appendix
   *   Indicating if we're loading the kamerstuk itself or the appendix.
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string|null $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin ID.
   *
   * @return \Drupal\Core\Link[]
   *   An array with links to the appendixes.
   */
  protected function getKamerstukAppendixesLinks(bool $appendix, string $type, int $year, ?string $phase = NULL, ?string $hoofdstukMinfinId = NULL): array {
    $links = [];

    if (!$appendix) {
      $routeParams = ['year' => $year];
      if ($phase) {
        $routeParams['phase'] = $phase;
      }
      if ($hoofdstukMinfinId) {
        $routeParams['hoofdstukMinfinId'] = $hoofdstukMinfinId;
      }
      $url = Url::fromRoute('minfin.' . $type . '.appendix', $routeParams);
      if ($url->access()) {
        $links[] = Link::fromTextAndUrl('Bijlagen ' . $this->getName(), $url);
      }
    }

    return $links;
  }

}
