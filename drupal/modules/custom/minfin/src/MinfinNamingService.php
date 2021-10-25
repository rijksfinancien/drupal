<?php

namespace Drupal\minfin;

use Drupal\Core\Database\Connection;

/**
 * Service for minfin related namingconventions.
 */
class MinfinNamingService implements MinfinNamingServiceInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function getVuoName(string $vuo): string {
    if (strtoupper($vuo) === 'V') {
      return 'Verplichtingen';
    }
    if (strtoupper($vuo) === 'U') {
      return 'Uitgaven';
    }
    if (strtoupper($vuo) === 'O') {
      return 'Ontvangsten';
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getDocumentType(string $type, string $phase): ?string {
    switch ($type) {
      case 'miljoenennota':
        return 'Miljoenennota';

      case 'financieel_jaarverslag':
        return 'Financieel jaarverslag';

      case 'voorjaarsnota':
        return 'Voorjaarsnota';

      case 'najaarsnota':
        return 'Najaarsnota';

      case 'jaarverslag':
        return 'Jaarverslag';

      case 'memorie_van_toelichting':
        switch (strtoupper($phase)) {
          case 'OWB':
            return 'Begroting';

          case '1SUPP':
          case 'O1':
            return '1e suppletoire';

          case '2SUPP':
          case 'O2':
            return '2e suppletoire';

          case 'JV':
            return 'Slotwet';
        }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFiscalPhase(string $type, string $phase): ?string {
    switch ($type) {
      case 'miljoenennota':
      case 'belastingplan_memorie_van_toelichting':
        return 'Voorbereiding';

      case 'voorjaarsnota':
      case 'najaarsnota':
        return 'Uitvoering';

      case 'jaarverslag':
      case 'financieel_jaarverslag':
        return 'Verantwoording';

      case 'memorie_van_toelichting':
        switch (strtoupper($phase)) {
          case 'OWB':
            return 'Voorbereiding';

          case '1SUPP':
          case '2SUPP':
            return 'Uitvoering';

          case 'JV':
            return 'Verantwoording';
        }
        break;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFaseName(string $fase): string {
    $fase = strtoupper($fase);
    if ($fase === 'OWB') {
      return 'Begroting';
    }
    if ($fase === 'JV') {
      return 'Jaarverslag';
    }
    if ($fase === '1SUPP' || $fase === 'O1') {
      return '1e suppletoire';
    }
    if ($fase === '2SUPP' || $fase === 'O2') {
      return '2e suppletoire';
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getIsbName(string $fase, ?int $jaar = NULL, ?string $hoofdstukMinfinId = NULL): string {
    if ($jaar && $hoofdstukMinfinId) {
      $record = $this->connection->select('mf_kamerstuk_isb_title', 'kit')
        ->fields('kit', ['naam', 'date'])
        ->condition('kit.fase', $fase, '=')
        ->condition('kit.jaar', $jaar, '=')
        ->condition('kit.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
        ->execute()->fetchAssoc();
      if ($record) {
        if ($record['date']) {
          return $record['naam'] . ' | ' . date('d-m-Y', strtotime($record['date']));
        }
        return $record['naam'];
      }
    }

    $rename = [
      'ISB1' => 'Eerste ISB',
      'ISB2' => 'Tweede ISB',
      'ISB3' => 'Derde ISB',
      'ISB4' => 'Vierde ISB',
      'ISB5' => 'Vijfde ISB',
      'ISB6' => 'Zesde ISB',
      'ISB7' => 'Zevende ISB',
      'ISB8' => 'Achtste ISB',
      'ISB9' => 'Negende ISB',
      'ISB10' => 'Tiende ISB',
      'ISB11' => 'Elfde ISB',
      'ISB12' => 'Twaalfde ISB',
      'ISB13' => 'Dertiende ISB',
    ];

    return $rename[$fase] ?? $fase;
  }

  /**
   * {@inheritdoc}
   */
  public function getHoofdstukName(int $jaar, string $hoofdstukMinfinId, bool $prefix = FALSE): string {
    $name = $this->connection->select('mf_hoofdstuk', 'h')
      ->fields('h', ['naam'])
      ->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
      ->condition('h.jaar', $jaar, '=')
      ->execute()->fetchField();

    return $name ? ($prefix ? $hoofdstukMinfinId . ' ' : '') . $name : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getArtikelName(int $jaar, string $hoofdstukMinfinId, string $artikelMinfinId): string {
    $name = $this->connection->select('mf_artikel', 'a')
      ->fields('a', ['naam'])
      ->condition('a.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
      ->condition('a.artikel_minfin_id', $artikelMinfinId, '=')
      ->condition('a.jaar', $jaar, '=')
      ->execute()->fetchField();

    return $name ?? '';
  }

}
