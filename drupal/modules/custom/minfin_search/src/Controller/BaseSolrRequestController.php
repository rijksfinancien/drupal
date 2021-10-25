<?php

namespace Drupal\minfin_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\minfin\MinfinServiceInterface;
use Drupal\minfin_search\Entity\SolrSearchResult;
use Drupal\minfin_search\SearchUrlTrait;
use Drupal\minfin_solr\SolrClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines a base controller for solr search request controllers.
 */
abstract class BaseSolrRequestController extends ControllerBase {

  use SearchUrlTrait;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The SOLR client.
   *
   * @var \Drupal\minfin_solr\SolrClientInterface
   */
  protected $solrClient;

  /**
   * The Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The minfin service.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * SearchController constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The Renderer.
   * @param \Drupal\minfin_solr\SolrClientInterface $solrClient
   *   The SOLR client.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   The minfin service.
   */
  public function __construct(RendererInterface $renderer, SolrClientInterface $solrClient, RequestStack $requestStack, MinfinServiceInterface $minfinService) {
    $this->renderer = $renderer;
    $this->solrClient = $solrClient;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->minfinService = $minfinService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('minfin_solr.solr_client'),
      $container->get('request_stack'),
      $container->get('minfin.minfin'),
    );
  }

  /**
   * Helper function to retreive the general build array for a search record.
   *
   * @param int $id
   *   The id of the row.
   * @param array $values
   *   The values as returned by SOLR.
   * @param array $selectedValues
   *   An array with the current state of the select options.
   * @param bool $activeSuggestion
   *   Is this the active record (used for updating default values).
   *
   * @return array
   *   A Drupal render array.
   */
  protected function buildSearchRecord(int $id, array $values, array $selectedValues = [], bool $activeSuggestion = FALSE): array {
    $solrResult = new SolrSearchResult($values);
    $title = $solrResult->getTitle();
    $chapterId = $values['fiscal_chapter_id'] ?? '';
    $documentType = $selectedValues['document_type'] ?? NULL;
    $selectedYear = $selectedValues['year'] ?? NULL;

    $build = [
      '#id' => $id,
      '#badges' => [],
    ];

    if ($documentTypes = $solrResult->getDocumentTypes()) {
      $build['#badges']['documentTypes'] = [
        '#type' => 'select',
        '#attributes' => [
          'class' => ['search-badge'],
          'data-key' => 'document_type',
          'data-default-value' => $this->getDefaultValue($activeSuggestion, $selectedValues['document_type'] ?? NULL, $documentTypes),
        ],
        '#options' => $documentTypes,
      ];
    }
    if (!$documentType && isset($build['#badges']['documentTypes']['#attributes']['data-default-value'])) {
      $documentType = $build['#badges']['documentTypes']['#attributes']['data-default-value'];
    }

    $years = [];
    if ($chapterId && $documentType && ($solrYears = $solrResult->getYears())) {
      foreach ($solrYears as $year) {
        if (($url = $this->minfinService->getChapterUrl($chapterId, $year, $documentType)) && $url->access()) {
          $years[$year] = $year;
        }
      }
      arsort($years);
      $build['#badges']['years'] = [
        '#type' => 'select',
        '#attributes' => [
          'class' => ['search-badge'],
          'data-key' => 'year',
          'data-default-value' => $this->getDefaultValue($activeSuggestion, $selectedValues['year'] ?? NULL, $years),
        ],
        '#chosen' => TRUE,
        '#options' => $years,
      ];
    }
    if (!$selectedYear || !in_array($selectedYear, $years)) {
      $selectedYear = key($years);
    }

    if (count($documentTypes) > 1 || count($years) > 1) {
      $selectedValues['document_type'] = $documentType;
      $selectedValues['year'] = $selectedYear;
    }
    $build['#link'] = [
      '#type' => 'markup',
      '#markup' => "<a href='" . $solrResult->getUrl($selectedValues) . "'>" . $title . '</a>',
    ];

    return $build;
  }

  /**
   * Helper function to get the default value.
   *
   * @param bool $activeSuggestion
   *   Is this the active search suggestion.
   * @param string|int|mixed $defaultValue
   *   The default value.
   * @param array $values
   *   A list with all available values.
   *
   * @return string|int
   *   The default value.
   */
  private function getDefaultValue(bool $activeSuggestion, $defaultValue, array $values) {
    if ($activeSuggestion && $defaultValue && !empty($values[$defaultValue])) {
      return $defaultValue;
    }
    return reset($values);
  }

}
