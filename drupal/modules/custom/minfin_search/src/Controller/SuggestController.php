<?php

namespace Drupal\minfin_search\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Defines the suggest controller.
 */
class SuggestController extends BaseSolrRequestController {

  /**
   * Requests a suggestion list from solr and sends it to the template.
   *
   * @param string $term
   *   The search term.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The ajax response.
   */
  public function resultList($term): Response {
    $term = urldecode($term);

    $suggestions = [];
    $queryParams = $this->currentRequest->query->all();

    $id = (int) ($queryParams['suggestion'] ?? 0);
    $counter = 0;

    foreach ($this->solrClient->getSuggestions($term) as $values) {
      $activeSuggestion = ($counter === $id);

      $selectedValues = $queryParams;
      unset($selectedValues['suggestion']);

      $suggestion = $this->buildSearchRecord($counter, $values, $selectedValues, $activeSuggestion);
      $suggestion['#theme'] = 'minfin_search_suggestion';
      $suggestion['#header'] = $counter === 0 ? $this->t('Most important suggestion') : NULL;

      $suggestions[] = $suggestion;
      $counter++;
    }

    $build = [
      '#theme' => 'minfin_search_suggester',
      '#suggestions' => $suggestions,
    ];

    $html = $this->renderer->renderRoot($build);
    $response = new Response();
    $response->setContent($html);

    return $response;
  }

}
