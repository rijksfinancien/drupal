<?php

namespace Drupal\minfin_beleidsevaluaties\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates the page for a single beleidsevaluatie.
 */
class BeleidsevaluatieController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

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
   * Get the title for the page.
   *
   * @param string $beleidsevaluatieId
   *   The beleidsevaluatie id.
   *
   * @return string
   *   The title.
   */
  public function title(string $beleidsevaluatieId): string {
    $title = $this->connection->select('mf_beleidsevaluatie', 'b')
      ->fields('b', ['titel'])
      ->condition('b.beleidsevaluatie_id', $beleidsevaluatieId, '=')
      ->execute()->fetchField();
    return $title ?? '';
  }

  /**
   * Build the actual page.
   *
   * @param string $beleidsevaluatieId
   *   The beleidsevaluatie id.
   *
   * @return array
   *   A drupal render array.
   */
  public function content(string $beleidsevaluatieId): array {
    $data = $this->connection->select('mf_beleidsevaluatie', 'b')
      ->fields('b', [
        'opleverdatum',
        'type',
        'onafhankelijke_deskundige',
        'status',
        'sea',
        'aankondiging',
        'hoofdrapport',
        'kabinetsreactie_aanbiedingsbrief',
        'toelichting',
        'departement',
      ])
      ->condition('b.beleidsevaluatie_id', $beleidsevaluatieId, '=')
      ->execute()->fetchAssoc();
    $data['bijlage'] = [];
    $data['hoofdstuk'] = [];
    $data['artikel'] = [];
    $data['thema'] = [];
    $data['sea'] = $this->createExternalLink($data['sea']);
    $data['aankondiging'] = $this->createExternalLink($data['aankondiging']);
    $data['hoofdrapport'] = $this->createExternalLink($data['hoofdrapport']);
    $data['kabinetsreactie_aanbiedingsbrief'] = $this->createExternalLink($data['kabinetsreactie_aanbiedingsbrief']);

    $result = $this->connection->select('mf_beleidsevaluatie_bijlage', 'b')
      ->fields('b', ['bijlage'])
      ->condition('b.beleidsevaluatie_id', $beleidsevaluatieId, '=')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data['bijlage'][] = $this->createExternalLink($record['bijlage']);
    }

    return [
      '#theme' => 'beleidsevaluatie',
      '#beleidsevaluatie' => $data,
    ];
  }

  /**
   * Create an external link.
   *
   * @param string|null $uri
   *   The external uri.
   *
   * @return \Drupal\Core\Link|null
   *   The link.
   */
  private function createExternalLink(?string $uri): ?Link {
    if (!$uri) {
      return NULL;
    }

    if (UrlHelper::isValid($uri)) {
      $url = Url::fromUri($uri, ['external' => TRUE]);
      return Link::fromTextAndUrl($uri, $url);
    }
    return NULL;
  }

}
