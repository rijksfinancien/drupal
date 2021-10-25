<?php

namespace Drupal\minfin_visuals;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\minfin\MinfinNamingServiceInterface;

/**
 * Class to define the menu_link breadcrumb builder.
 */
class BreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * Connection.
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
   * BreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The translationinterface.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNamingService
   *   The minfin naming service.
   */
  public function __construct(TranslationInterface $stringTranslation, Connection $connection, MinfinNamingServiceInterface $minfinNamingService) {
    $this->stringTranslation = $stringTranslation;
    $this->connection = $connection;
    $this->minfinNamingService = $minfinNamingService;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() === 'minfin_visuals';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $params = $route_match->getParameters();
    $vuo = $params->get('vuo');
    $fase = $params->get('fase');
    $jaar = $params->get('jaar');
    $hoofdstukMinfinId = $params->get('hoofdstukMinfinId');
    $artikelMinfinId = $params->get('artikelMinfinId');
    $sub1 = $params->get('sub1');
    $sub2 = $params->get('sub2');
    $sub3 = $params->get('sub3');

    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['url.path']);
    $breadcrumb->addCacheableDependency(0);

    $links = [];
    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    if (isset($hoofdstukMinfinId)) {
      $route_params = [
        'jaar' => $jaar,
        'fase' => $params->get('fase'),
        'vuo' => $params->get('vuo'),
      ];
      $links[] = Link::createFromRoute(ucfirst($fase) . ' ' . $jaar . ' ' . ucfirst($vuo), 'minfin_visuals', $route_params);
    }

    if (isset($artikelMinfinId)) {
      $route_params['hoofdstukMinfinId'] = $hoofdstukMinfinId;
      $title = $this->getName($jaar, $hoofdstukMinfinId);
      $links[] = Link::createFromRoute($title, 'minfin_visuals', $route_params);
    }

    if (isset($sub1)) {
      $route_params['artikelMinfinId'] = $artikelMinfinId;
      $title = $this->getName($jaar, $hoofdstukMinfinId, $artikelMinfinId);
      $links[] = Link::createFromRoute($title, 'minfin_visuals', $route_params);
    }

    if (isset($sub2)) {
      $route_params['sub1'] = $sub1;
      $title = $this->getName($jaar, $hoofdstukMinfinId, $artikelMinfinId, $sub1);
      $links[] = Link::createFromRoute($title, 'minfin_visuals', $route_params);
    }

    if (isset($sub3)) {
      $route_params['sub2'] = $sub2;
      $title = $this->getName($jaar, $hoofdstukMinfinId, $artikelMinfinId, $sub1, $sub2);
      $links[] = Link::createFromRoute($title, 'minfin_visuals', $route_params);
    }

    return $breadcrumb->setLinks($links);
  }

  /**
   * Get the requested name.
   *
   * @param int $jaar
   *   Jaar.
   * @param string|null $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   Artikel minfin id.
   * @param string|null $artikelonderdeelMinfinId
   *   Artikelonderdeel minfin id.
   * @param string|null $instrumentOfUitsplitsingApparaatMinfinId
   *   Instrument of uitsplitsing apparaat minfin id.
   * @param string|null $regelingDetailniveauMinfinId
   *   Regeling detailniveau minfin id.
   *
   * @return string|null
   *   The name or null if no name was found.
   */
  private function getName($jaar, $hoofdstukMinfinId = NULL, $artikelMinfinId = NULL, $artikelonderdeelMinfinId = NULL, $instrumentOfUitsplitsingApparaatMinfinId = NULL, $regelingDetailniveauMinfinId = NULL): ?string {
    $query = $this->connection->select('mf_b_tabel', 'bt');
    $query->leftJoin('mf_regeling_detailniveau', 'rd', 'rd.regeling_detailniveau_id = bt.regeling_detailniveau_id');
    $query->leftJoin('mf_instrument_of_uitsplitsing_apparaat', 'iua', 'iua.instrument_of_uitsplitsing_apparaat_id = bt.instrument_of_uitsplitsing_apparaat_id');
    $query->leftJoin('mf_artikelonderdeel', 'ao', 'ao.artikelonderdeel_id = bt.artikelonderdeel_id');
    $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id');
    $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id');
    $query->condition('bt.jaar', $jaar, '=');

    if (isset($regelingDetailniveauMinfinId)) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
      $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $instrumentOfUitsplitsingApparaatMinfinId, '=');
      $query->condition('rd.regeling_detailniveau_minfin_id', $regelingDetailniveauMinfinId, '=');
      $query->addField('rd', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $hoofdstukMinfinId, $artikelMinfinId, $artikelonderdeelMinfinId, $instrumentOfUitsplitsingApparaatMinfinId, NULL);
    }
    if (isset($instrumentOfUitsplitsingApparaatMinfinId)) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
      $query->condition('iua.instrument_of_uitsplitsing_apparaat_minfin_id', $instrumentOfUitsplitsingApparaatMinfinId, '=');
      $query->addField('iua', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $hoofdstukMinfinId, $artikelMinfinId, $artikelonderdeelMinfinId, NULL, NULL);
    }
    if (isset($artikelonderdeelMinfinId)) {
      $query->condition('h.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->condition('a.artikel_minfin_id', $artikelMinfinId, '=');
      $query->condition('ao.artikelonderdeel_minfin_id', $artikelonderdeelMinfinId, '=');
      $query->addField('ao', 'naam');
      if ($name = $query->execute()->fetchField()) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $hoofdstukMinfinId, $artikelMinfinId, NULL, NULL, NULL);
    }

    if (!empty($artikelMinfinId)) {
      if ($name = $this->minfinNamingService->getArtikelName($jaar, $hoofdstukMinfinId, $artikelMinfinId)) {
        return $name;
      }
      // If empty get the parents name.
      return $this->getName($jaar, $hoofdstukMinfinId, NULL, NULL, NULL, NULL);
    }

    if ($name = $this->minfinNamingService->getHoofdstukName($jaar, $hoofdstukMinfinId)) {
      return $name;
    }

    return NULL;
  }

}
