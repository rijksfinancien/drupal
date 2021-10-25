<?php

namespace Drupal\minfin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * Service for common minfin related functions.
 */
class MinfinService implements MinfinServiceInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The currently active route match object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(Connection $connection, RouteMatchInterface $routeMatch, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager) {
    $this->connection = $connection;
    $this->routeMatch = $routeMatch;
    $this->config = $configFactory->get('minfin.chapter_sorting');
    $this->fileStorage = $entityTypeManager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public function getFirstYear(): int {
    return min($this->getAvailableYears());
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableYears(): array {
    // @todo we can probably add some decent long term caching to this function.
    $values = &drupal_static(__METHOD__);
    if (!$values) {
      $values = [];
      $query = $this->connection->select('mf_b_tabel', 'bt');
      $query->fields('bt', ['jaar']);
      $query->distinct(TRUE);
      foreach ($query->execute() as $record) {
        $values[$record->jaar] = (int) $record->jaar;
      }

      $query = $this->connection->select('mf_begrotingsstaat', 'b');
      $query->fields('b', ['jaar']);
      $query->distinct(TRUE);
      foreach ($query->execute() as $record) {
        $values[$record->jaar] = (int) $record->jaar;
      }

      $query = $this->connection->select('mf_kamerstuk', 'k');
      $query->fields('k', ['jaar']);
      $query->distinct(TRUE);
      foreach ($query->execute() as $record) {
        $values[$record->jaar] = (int) $record->jaar;
      }

      $query = $this->connection->select('mf_kamerstuk_files', 'kf');
      $query->fields('kf', ['jaar']);
      $query->distinct(TRUE);
      foreach ($query->execute() as $record) {
        $values[$record->jaar] = (int) $record->jaar;
      }

      // Just a fallback to prevent the code from crashing.
      if (empty($values)) {
        $values[1] = 1;
      }

      krsort($values);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getChaptersForYear($year = NULL): array {
    if (!$year) {
      $year = $this->getActiveYear();
    }

    $data = [];
    $result = $this->connection->select('mf_hoofdstuk', 'h')
      ->fields('h', ['hoofdstuk_minfin_id', 'naam'])
      ->condition('h.jaar', $year, '=')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data[$record['hoofdstuk_minfin_id']] = $record['naam'];
    }

    $sort = $this->config->get('chapters') ?? [];
    uksort($data, static function ($key1, $key2) use ($sort) {
      return (int) ($sort[$key1] ?? 0) <=> (int) ($sort[$key2] ?? 0);
    });

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveYear(): int {
    return $this->routeMatch->getParameter('year') ?? $this->getLastYear();
  }

  /**
   * {@inheritdoc}
   */
  public function getLastYear(): int {
    return max($this->getAvailableYears());
  }

  /**
   * {@inheritdoc}
   */
  public function getArtikelMinfinIdFromKamerstukAnchor(string $type, int $year, string $phase, ?string $hoofdstukMinfinId, string $anchor): ?string {
    $query = $this->connection->select('mf_kamerstuk', 'k');
    $query->fields('k', ['artikel_minfin_id']);
    $query->condition('k.type', $type, '=');
    $query->condition('k.jaar', $year, '=');
    $query->condition('k.anchor', $anchor, '=');
    $query->condition('k.fase', $phase, '=');
    if ($hoofdstukMinfinId) {
      $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    }
    $artikelMinfinId = $query->execute()->fetchField();

    return $artikelMinfinId ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildKamerstukUrl(string $routePrefix, int $year, array $options = [], ?string $phase = NULL, ?string $hoofdstukMinfinId = NULL, ?string $artikelMinfinId = NULL): ?Url {
    $anchor = NULL;
    $type = substr($routePrefix, 7);

    if (in_array($type, ['memorie_van_toelichting', 'jaarverslag', 'isb_memorie_van_toelichting'])) {
      if ($hoofdstukMinfinId) {
        $query = $this->connection->select('mf_kamerstuk', 'k');
        $query->fields('k', ['hoofdstuk_minfin_id']);
        $query->condition('k.type', $type, '=');
        if ($phase) {
          $query->condition('k.fase', $phase, '=');
        }
        elseif ($type === 'isb_memorie_van_toelichting') {
          $query->condition('k.fase', $phase . '%', 'LIKE');
        }
        $query->condition('k.jaar', $year, '=');
        $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
        $hoofdstukMinfinId = $query->execute()->fetchField();
      }

      if ($hoofdstukMinfinId && $artikelMinfinId) {
        $query = $this->connection->select('mf_kamerstuk', 'k');
        $query->fields('k', ['anchor']);
        $query->condition('k.type', $type, '=');
        if ($phase) {
          $query->condition('k.fase', $phase, '=');
        }
        elseif ($type === 'isb_memorie_van_toelichting') {
          $query->condition('k.fase', $phase . '%', 'LIKE');
        }
        $query->condition('k.jaar', $year, '=');
        $query->condition('k.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
        $query->condition('k.artikel_minfin_id', $artikelMinfinId, '=');
        $anchor = $query->execute()->fetchField();
      }
    }

    switch ($routePrefix) {
      case 'minfin.isb_memorie_van_toelichting':
      case 'minfin.memorie_van_toelichting':
        if ($routePrefix === 'minfin.isb_memorie_van_toelichting' && empty($phase)) {
          $phase = 'ISB';
        }

        $params = ['year' => $year, 'phase' => $phase];
        if ($hoofdstukMinfinId) {
          $params += ['hoofdstukMinfinId' => $hoofdstukMinfinId];
          if ($anchor) {
            $params += ['anchor' => $anchor];
            return Url::fromRoute($routePrefix . '.anchor', $params, $options);
          }
          return Url::fromRoute($routePrefix . '.table_of_contents', $params, $options);
        }
        return Url::fromRoute($routePrefix . '.overview', $params, $options);

      case 'minfin.jaarverslag':
        $params = ['year' => $year];
        if ($hoofdstukMinfinId) {
          $params += ['hoofdstukMinfinId' => $hoofdstukMinfinId];
          if ($anchor) {
            $params += ['anchor' => $anchor];
            return Url::fromRoute($routePrefix . '.anchor', $params, $options);
          }
          return Url::fromRoute($routePrefix . '.table_of_contents', $params, $options);
        }
        return Url::fromRoute($routePrefix . '.overview', $params, $options);

      case 'minfin.miljoenennota':
      case 'minfin.belastingplan_memorie_van_toelichting':
      case 'minfin.voorjaarsnota':
      case 'minfin.najaarsnota':
      case 'minfin.financieel_jaarverslag':
        $params = ['year' => $year];
        if ($anchor) {
          $params += ['anchor' => $anchor];
          return Url::fromRoute($routePrefix . '.anchor', $params, $options);
        }
        return Url::fromRoute($routePrefix . '.table_of_contents', $params, $options);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildKamerstuPdfkUrl(string $routePrefix, int $year, array $options = [], ?string $phase = NULL, ?string $hoofdstukMinfinId = NULL): ?Url {
    $type = substr($routePrefix, 7);

    $query = $this->connection->select('mf_kamerstuk_files', 'kf');
    $query->fields('kf', ['fid']);
    $query->condition('type', $type, '=');
    $query->condition('jaar', $year, '=');
    // @todo check if this phase check leads to issues.
    if ($phase) {
      $query->condition('fase', $phase, '=');
    }
    if ($hoofdstukMinfinId) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    }
    else {
      $query->isNull('hoofdstuk_minfin_id');
    }
    /** @var \Drupal\file\Entity\File $file */
    if (($fid = $query->execute()->fetchField()) && ($file = $this->fileStorage->load($fid))) {
      return Url::fromUri(file_create_url($file->getFileUri()));
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategories(): array {
    return [
      'Voorbereiding' => [
        'miljoenennota' => [
          'routePrefix' => 'minfin.miljoenennota',
          'text' => 'Miljoenennota',
        ],
        'belastingplan_memorie_van_toelichting' => [
          'routePrefix' => 'minfin.belastingplan_memorie_van_toelichting',
          'text' => 'Belastingplan',
        ],
        'begroting' => [
          'routePrefix' => 'minfin.memorie_van_toelichting',
          'text' => 'Begroting',
          'params' => [
            'phase' => 'OWB',
          ],
        ],
      ],
      'Uitvoering' => [
        'voorjaarsnota' => [
          'routePrefix' => 'minfin.voorjaarsnota',
          'text' => 'Voorjaarsnota',
        ],
        'najaarsnota' => [
          'routePrefix' => 'minfin.najaarsnota',
          'text' => 'Najaarsnota',
        ],
        '1SUPP' => [
          'routePrefix' => 'minfin.memorie_van_toelichting',
          'text' => '1e suppletoire',
          'params' => [
            'phase' => '1SUPP',
          ],
        ],
        '2SUPP' => [
          'routePrefix' => 'minfin.memorie_van_toelichting',
          'text' => '2e suppletoire',
          'params' => [
            'phase' => '2SUPP',
          ],
        ],
        'Incidenteel' => [
          'routePrefix' => 'minfin.isb_memorie_van_toelichting',
          'text' => 'Incidenteel',
        ],
      ],
      'Verantwoording' => [
        'financieel_jaarverslag' => [
          'routePrefix' => 'minfin.financieel_jaarverslag',
          'text' => 'Financieel jaarverslag',
        ],
        'jaarverslag' => [
          'routePrefix' => 'minfin.jaarverslag',
          'text' => 'Jaarverslag',
        ],
        'slotwet' => [
          'routePrefix' => 'minfin.memorie_van_toelichting',
          'text' => 'Slotwet',
          'params' => [
            'phase' => 'JV',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPhaseByDocumentType(string $documentType): ?string {
    switch (strtolower($documentType)) {
      case 'begroting':
        return 'OWB';

      case '1e suppletoire':
        return '1SUPP';

      case '2e suppletoire':
        return '2SUPP';

      case 'jaarverslag':
      case 'slotwet':
        return 'JV';
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeByDocumentType(string $documentType): ?string {
    switch (strtolower($documentType)) {
      case 'miljoenennota':
      case 'voorjaarsnota':
      case 'najaarsnota':
      case 'jaarverslag':
        return strtolower($documentType);

      case 'financieel jaarverslag':
        return 'financieel_jaarverslag';

      case 'begroting':
      case '1e suppletoire':
      case '2e suppletoire':
      case 'slotwet':
        return 'memorie_van_toelichting';
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getChapterUrl(string $chapterId, string $year, string $documentType = ''): Url {
    $routeParams = [
      'year' => $year,
      'hoofdstukMinfinId' => $chapterId,
    ];

    if ($documentType) {
      $documentType = strtolower($documentType);
      switch ($documentType) {
        case 'jaarverslag':
          return Url::fromRoute('minfin.jaarverslag.table_of_contents', $routeParams);

        default:
          if ($phase = $this->getPhaseByDocumentType($documentType)) {
            $routeParams['phase'] = $phase;
            return Url::fromRoute('minfin.memorie_van_toelichting.table_of_contents', $routeParams);
          }
          break;
      }
    }

    return Url::fromRoute('minfin_general.chapter', $routeParams);
  }

  /**
   * {@inheritdoc}
   */
  public function buildChapterLink(string $title, string $chapterId, string $year, string $documentType = ''): Link {
    $url = $this->getChapterUrl($chapterId, $year, $documentType);
    return Link::createFromRoute($title, $url->getRouteName(), $url->getRouteParameters());
  }

  /**
   * {@inheritdoc}
   */
  public function getMostRecentKamerstukUrl(array $options = [], ?string $hoofdstukMinfinId = NULL, ?string $artikelMinfinId = NULL): ?Url {
    if ($params = $this->getMostRecentKamerstukUrlParams()) {
      return $this->buildKamerstukUrl($params['route'], $params['year'], $options, $params['phase'], $hoofdstukMinfinId, $artikelMinfinId);
    }

    return NULL;
  }

  /**
   * Helper function for the getMostRecentKamerstukUrl function.
   *
   * @return array
   *   An array with the params.
   */
  private function getMostRecentKamerstukUrlParams(): array {
    // @todo we can probably add some decent long term caching to this function.
    $params = &drupal_static(__METHOD__);
    if (!$params) {
      foreach ($this->getAvailableYears() as $year) {
        $jaarverslag = $this->connection->select('mf_kamerstuk', 'k')
          ->fields('k', ['type'])
          ->condition('jaar', $year, '=')
          ->condition('type', 'jaarverslag', '=')
          ->execute()->fetchField();
        if ($jaarverslag) {
          $params = [
            'route' => 'minfin.jaarverslag',
            'phase' => NULL,
            'year' => $year,
          ];
          return $params;
        }

        $begroting = $this->connection->select('mf_kamerstuk', 'k')
          ->fields('k', ['type'])
          ->condition('jaar', $year, '=')
          ->condition('type', 'memorie_van_toelichting', '=')
          ->condition('fase', 'OWB', '=')
          ->execute()->fetchField();
        if ($begroting) {
          $params = [
            'route' => 'minfin.memorie_van_toelichting',
            'phase' => 'OWB',
            'year' => $year,
          ];
          return $params;
        }
      }
    }

    return $params;
  }

}
