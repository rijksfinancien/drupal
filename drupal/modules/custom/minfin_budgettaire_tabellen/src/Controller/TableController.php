<?php

namespace Drupal\minfin_budgettaire_tabellen\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\minfin\MinfinNamingServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The tabulair pageview.
 */
class TableController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The minfin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingServiceInterface
   */
  protected $minfinNamingService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_route_match'),
      $container->get('minfin.naming')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNamingService
   *   The minfin naming service.
   */
  public function __construct(Connection $connection, RouteMatchInterface $routeMatch, MinfinNamingServiceInterface $minfinNamingService) {
    $this->connection = $connection;
    $this->routeMatch = $routeMatch;
    $this->minfinNamingService = $minfinNamingService;
  }

  /**
   * Get the page title.
   *
   * @param string $fase
   *   Fase.
   * @param string $vuo
   *   Verplichtingen, Uitgaven, Ontvangsten.
   * @param int|null $jaar
   *   Jaar.
   * @param string|null $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   Artikel minfin id.
   *
   * @return string
   *   The page title.
   */
  public function getTitle($fase, $vuo, $jaar = NULL, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL): string {
    if (empty($jaar)) {
      return $this->minfinNamingService->getVuoName($vuo) . ' ' . $this->minfinNamingService->getFaseName($fase);
    }

    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->condition('bt.jaar', $jaar, '=');

    $name = NULL;
    if (empty($hoofdstukMinfinId)) {
      $name = $this->minfinNamingService->getVuoName($vuo) . ' ' . $this->minfinNamingService->getFaseName($fase) . ' ' . $jaar;
    }
    elseif (empty($artikelMinfinId)) {
      $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->addField('h', 'naam');
      $query->addField('h', 'hoofdstuk_minfin_id');
      $result = $query->execute()->fetchAssoc();
      $name = $this->minfinNamingService->getVuoName($vuo) . ' ' . $this->minfinNamingService->getFaseName($fase) . ' ' . $jaar . ': ' . $result['naam'];

    }
    elseif (!empty($artikelMinfinId)) {
      $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
      $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->addField('a', 'naam');
      $query->addField('a', 'artikel_minfin_id');
      $query->addField('h', 'hoofdstuk_minfin_id');
      $result = $query->execute()->fetchAssoc();
      $name = $this->minfinNamingService->getVuoName($vuo) . ' ' . $this->minfinNamingService->getFaseName($fase) . ' ' . $jaar . ': ' . $result['naam'];
    }

    return $name;
  }

  /**
   * Render a table view for the budgettaire tabellen.
   *
   * @param string $fase
   *   Fase.
   * @param string $vuo
   *   Verplichtingen, Uitgaven, Ontvangsten.
   * @param int|null $jaar
   *   Jaar.
   * @param string|null $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   Artikel minfin id.
   *
   * @return array
   *   A drupal render array.
   */
  public function renderTable($fase, $vuo, $jaar = NULL, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL): array {
    $routeName = $this->routeMatch->getRouteName();
    $routeParams = $this->routeMatch->getParameters()->all();

    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->join('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->join('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->join('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
    $query->condition('bt.vuo', $vuo, '=');

    if (empty($jaar)) {
      if (strtoupper($fase) === 'JV') {
        $query->condition('bt.bedrag_jaarverslag', 0, '>');
      }
      elseif (strtoupper($fase) === 'O1') {
        $query->condition('bt.bedrag_suppletoire1', 0, '>');
      }
      elseif (strtoupper($fase) === 'O2') {
        $query->condition('bt.bedrag_suppletoire2', 0, '>');
      }
      else {
        $query->condition('bt.bedrag_begroting', 0, '>');
      }
      $query->fields('bt', ['jaar']);
      $query->groupBy('bt.jaar');
      $query->orderBy('bt.jaar', 'desc');
      $result = $query->execute();
      $items = [];
      while ($item = $result->fetchField()) {
        $items[] = Link::createFromRoute($item, $routeName, array_merge($routeParams, ['jaar' => $item]), ['attributes' => ['class' => ['button button--secondary']]]);
      }

      $build = [
        '#theme' => 'table_overview',
        '#items' => $items,
      ];
      return $build;
    }

    if (strtoupper($fase) === 'JV') {
      $query->addExpression('SUM(bt.bedrag_jaarverslag)', 'bedrag');
      $query->condition('bt.bedrag_jaarverslag', 0, '>');
    }
    elseif (strtoupper($fase) === 'O1') {
      $query->addExpression('SUM(bt.bedrag_suppletoire1)', 'bedrag');
      $query->condition('bt.bedrag_suppletoire1', 0, '>');
    }
    elseif (strtoupper($fase) === 'O2') {
      $query->addExpression('SUM(bt.bedrag_suppletoire2)', 'bedrag');
      $query->condition('bt.bedrag_suppletoire2', 0, '>');
    }
    else {
      $query->addExpression('SUM(bt.bedrag_begroting)', 'bedrag');
      $query->condition('bt.bedrag_begroting', 0, '>');
    }
    $query->condition('bt.jaar', $jaar, '=');

    if (empty($artikelMinfinId)) {
      $header = [
        'minfin_id' => [
          'data' => 'Nummer',
          'field' => 'minfin_id',
        ],
        'naam' => [
          'data' => 'Naam',
          'field' => 'naam',
        ],
        'bedrag' => [
          'data' => Markup::create('Bedrag <span>(x1.000)</span>'),
          'field' => 'bedrag',
        ],
      ];

      if (empty($hoofdstukMinfinId)) {
        $query->addField('h', 'hoofdstuk_minfin_id', 'minfin_id');
        $query->addField('h', 'naam', 'naam');
        $query->groupBy('h.naam');
        $query->groupBy('h.hoofdstuk_minfin_id');

        $header['minfin_id']['data'] = 'Begrotingshoofdstuk';
        $header['naam']['data'] = 'Hoofdstuk naam';
      }
      else {
        $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
        $query->addField('a', 'artikel_minfin_id', 'minfin_id');
        $query->addField('a', 'naam', 'naam');
        $query->groupBy('a.naam');
        $query->groupBy('h.hoofdstuk_minfin_id');
        $query->groupBy('a.artikel_minfin_id');

        $header['minfin_id']['data'] = 'Artikelnummer';
        $header['naam']['data'] = 'Artikel naam';
      }

      $query = $query->extend(TableSortExtender::class)->orderByHeader($header);
      $result = $query->execute();
      $rows = [];
      while ($record = $result->fetchAssoc()) {
        $params = $routeParams;

        if (empty($hoofdstukMinfinId)) {
          $params['hoofdstukMinfinId'] = $record['minfin_id'];
        }
        else {
          $params['artikelMinfinId'] = $record['minfin_id'];
        }

        if (!empty($record['bedrag'])) {
          $record['minfin_id'] = Link::createFromRoute($record['minfin_id'], $routeName, $params);
          $record['naam'] = Link::createFromRoute($record['naam'], $routeName, $params);
        }
        $rows[] = $record;
      }
    }
    else {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->addField('ao', 'naam', 'naam');
      $query->addField('iua', 'naam', 'naam_2');
      $query->addField('rd', 'naam', 'naam_3');
      $query->groupBy('ao.naam');
      $query->groupBy('iua.naam');
      $query->groupBy('rd.naam');
      $query->groupBy('h.hoofdstuk_minfin_id');
      $query->groupBy('a.artikel_minfin_id');
      $query->groupBy('ao.artikelonderdeel_minfin_id');
      $query->groupBy('iua.instrument_of_uitsplitsing_apparaat_minfin_id');
      $query->groupBy('rd.regeling_detailniveau_minfin_id');

      $header = [
        [
          'data' => 'Artikelnummer onderverdeling',
          'field' => 'naam',
        ],
        [
          'data' => 'Instrument of uitsplitsing apparaat',
          'field' => 'naam_2',
        ],
        [
          'data' => 'Regeling detailniveau',
          'field' => 'naam_3',
        ],
        [
          'data' => Markup::create('Bedrag <span>(x1.000)</span>'),
          'field' => 'bedrag',
        ],
      ];

      $query = $query->extend(TableSortExtender::class)->orderByHeader($header);
      $result = $query->execute();
      $rows = [];
      while ($record = $result->fetchAssoc()) {
        $record['naam_2'] = !empty($record['naam_2']) ? $record['naam_2'] : '-';
        $record['naam_3'] = !empty($record['naam_3']) ? $record['naam_3'] : '-';
        $rows[] = $record;
      }
    }

    $build = [];
    $build['container'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['container'],
        'id' => ['budget-tables'],
      ],
    ];
    $build['container']['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    $build['container']['backlinkg'] = $this->getBackLink($fase, $vuo, $hoofdstukMinfinId, $artikelMinfinId);

    return $build;

  }

  /**
   * Get the page title.
   *
   * @param string $fase
   *   Fase.
   * @param string $vuo
   *   Verplichtingen, Uitgaven, Ontvangsten.
   * @param string|null $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   Artikel minfin id.
   *
   * @return array
   *   A drupal render array.
   */
  private function getBackLink($fase, $vuo, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL): array {
    $routeName = $this->routeMatch->getRouteName();
    $routeParams = $this->routeMatch->getParameters()->all();

    if (empty($hoofdstukMinfinId)) {
      unset($routeParams['jaar']);
      return [
        '#title' => 'Terug naar ' . $this->minfinNamingService->getVuoName($vuo) . ' ' . $this->minfinNamingService->getFaseName($fase),
        '#type' => 'link',
        '#url' => Url::fromRoute($routeName, $routeParams),
      ];
    }
    if (empty($artikelMinfinId)) {
      unset($routeParams['hoofdstukMinfinId']);
      return [
        '#title' => 'Terug naar hoofdstukken',
        '#type' => 'link',
        '#url' => Url::fromRoute($routeName, $routeParams),
      ];
    }
    if (!empty($artikelMinfinId)) {
      unset($routeParams['artikelMinfinId']);
      return [
        '#title' => 'Terug naar artikelen',
        '#type' => 'link',
        '#url' => Url::fromRoute($routeName, $routeParams),
      ];
    }

    return [];
  }

}
