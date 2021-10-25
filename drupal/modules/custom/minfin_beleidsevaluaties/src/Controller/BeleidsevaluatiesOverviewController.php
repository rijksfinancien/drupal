<?php

namespace Drupal\minfin_beleidsevaluaties\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\minfin\MinfinServiceInterface;
use Drupal\minfin_beleidsevaluaties\Form\BeleidsevaluatiesFilterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines an overview for beleidsevaluaties.
 */
class BeleidsevaluatiesOverviewController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The minfin service.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('minfin.minfin'),
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   The minfin service.
   */
  public function __construct(Connection $connection, RequestStack $requestStack, MinfinServiceInterface $minfinService) {
    $this->connection = $connection;
    $this->request = $requestStack->getCurrentRequest();
    $this->minfinService = $minfinService;
  }

  /**
   * Render the page.
   *
   * @return array
   *   A drupal render array.
   */
  public function view(): array {
    $filters = $this->request->query->all();

    $rows = [];
    $header = [
      'titel' => [
        'data' => $this->t('Title', [], ['context' => 'minfin_beleidsevaluaties']),
        'field' => 'titel',
      ],
      'hoofdstuk_naam' => [
        'data' => ' Departement',
      ],
      'artikel_naam' => [
        'data' => ' Artikel',
      ],
      'thema' => [
        'data' => 'Thema',
      ],
      'type' => [
        'data' => 'Type onderzoek',
        'field' => 'type',
      ],
      'status' => [
        'data' => 'Status',
        'field' => 'status',
      ],
      'opleverdatum' => [
        'data' => 'Afronding',
        'field' => 'opleverdatum',
        'sort' => 'desc',
      ],
    ];

    $query = $this->connection->select('mf_beleidsevaluatie', 'b');
    $query->fields('b', [
      'beleidsevaluatie_id',
      'type',
      'titel',
      'opleverdatum',
      'status',
    ]);
    if (!empty($filters['titel'])) {
      $query->condition('b.titel', $filters['titel'], 'IN');
    }
    if (!empty($filters['opleverdatum'])) {
      $query->condition('b.opleverdatum', $filters['opleverdatum'], 'IN');
    }
    if (!empty($filters['type'])) {
      $query->condition('b.type', $filters['type'], 'IN');
    }
    if (!empty($filters['status'])) {
      $query->condition('b.status', $filters['status'], 'IN');
    }
    if (!empty($filters['hoofdstuk_naam'])) {
      $query->join('mf_beleidsevaluatie_hoofdstuk', 'bh', 'bh.beleidsevaluatie_id = b.beleidsevaluatie_id');
      $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bh.hoofdstuk_id');
      $query->condition('h.naam', $filters['hoofdstuk_naam'], 'IN');
    }
    if (!empty($filters['artikel_naam'])) {
      $query->join('mf_beleidsevaluatie_artikel', 'ba', 'ba.beleidsevaluatie_id = b.beleidsevaluatie_id');
      $query->join('mf_artikel', 'a', 'a.artikel_id = ba.artikel_id');
      $query->condition('a.naam', $filters['artikel_naam'], 'IN');
    }
    $query = $query->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
    $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $rows[] = [
        $record['beleidsevaluatie_id'] ? Link::createFromRoute($record['titel'], 'minfin_beleidsevaluaties.beleidsonderzoek', ['beleidsevaluatieId' => $record['beleidsevaluatie_id']]) : $record['titel'],
        [
          'data' => Markup::create(implode(', ', $this->getHoofdstukken($record['beleidsevaluatie_id']))),
          'width' => '25%',
          'class' => ['toggle-seperated-list'],
          'data-seperated-list-limit' => 3,
        ],
        [
          'data' => Markup::create(implode(', ', $this->getArtikelen($record['beleidsevaluatie_id']))),
          'width' => '15%',
          'class' => ['toggle-seperated-list'],
          'data-seperated-list-limit' => 2,
        ],
        '',
        $record['type'],
        $record['status'],
        $record['opleverdatum'],
      ];
    }

    return [
      '#theme' => 'beleidsevaluatie_overview',
      '#filters' => $this->formBuilder()->getForm(BeleidsevaluatiesFilterForm::class),
      '#table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => 'Geen resultaten gevonden.',
      ],
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
  }

  /**
   * Get the hoofdstukkken for a given beleidsevaluatie.
   *
   * @param string $beleidsevaluatieId
   *   The beleidsevaluatie id.
   *
   * @return array
   *   A list with hoofdstukken.
   */
  private function getHoofdstukken($beleidsevaluatieId): array {
    $hoofdstukken = [];
    $query = $this->connection->select('mf_beleidsevaluatie_hoofdstuk', 'bh');
    $query->join('mf_hoofdstuk', 'h', 'bh.hoofdstuk_id = h.hoofdstuk_id');
    $query->fields('h', ['naam', 'hoofdstuk_minfin_id']);
    $query->condition('bh.beleidsevaluatie_id', $beleidsevaluatieId, '=');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $hoofdstuk = $record['naam'];
      if ($url = $this->minfinService->getMostRecentKamerstukUrl([], $record['hoofdstuk_minfin_id'])) {
        $hoofdstuk = Link::fromTextAndUrl($record['naam'], $url)->toString();
      }
      $hoofdstukken[] = $hoofdstuk;
    }

    return $hoofdstukken;
  }

  /**
   * Get the artikelen for a given beleidsevaluatie.
   *
   * @param string $beleidsevaluatieId
   *   The beleidsevaluatie id.
   *
   * @return array
   *   A list with artikelen.
   */
  private function getArtikelen($beleidsevaluatieId): array {
    $artikelen = [];
    $query = $this->connection->select('mf_beleidsevaluatie_artikel', 'ba');
    $query->join('mf_artikel', 'a', 'ba.artikel_id = a.artikel_id');
    $query->fields('a', ['naam', 'hoofdstuk_minfin_id', 'artikel_minfin_id']);
    $query->condition('ba.beleidsevaluatie_id', $beleidsevaluatieId, '=');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $artikel = $record['naam'];
      if ($url = $this->minfinService->getMostRecentKamerstukUrl([], $record['hoofdstuk_minfin_id'], $record['artikel_minfin_id'])) {
        $artikel = Link::fromTextAndUrl($record['naam'], $url)->toString();
      }
      $artikelen[] = $artikel;
    }

    return $artikelen;
  }

}
