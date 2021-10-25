<?php

namespace Drupal\minfin_search\Entity;

use Drupal\Component\Utility\Html;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Defines a SOLR search result.
 */
class SolrSearchResult extends SolrResult implements SolrSearchResultInterface {

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $text = $this->values['contents'][0] ?? '';
    if (!empty($this->values['highlighting'])) {
      $text = strip_tags($this->values['highlighting'][0], '<span>') . '...';
    }
    else {
      $text = strip_tags($text, '<span> <b> <p>');
      $text = trim(preg_replace('/\s\s+/', ' ', $text));

      if (strlen($text) > 300) {
        $summary = '';
        foreach (explode(' ', $text) as $word) {
          $summary .= $word . ' ';

          if (strlen($summary) >= 300) {
            return Markup::create(trim($summary));
          }
        }
      }
    }

    return Markup::create(Html::normalize($text));
  }

  /**
   * {@inheritdoc}
   */
  public function getQuickLinks(array $selectedValues = []): array {
    $quickLinks = [];
    $chapterId = $this->values['fiscal_chapter_id'] ?? '';
    $documentType = $selectedValues['document_type'] ?? (key($this->getDocumentTypes()));

    // Retrieve only the years that are accessible.
    $years = [];
    if ($chapterId && $solrYears = $this->getYears()) {
      foreach ($solrYears as $year) {
        if (($url = $this->minfinService->getChapterUrl($chapterId, $year, $documentType ?? '')) && $url->access()) {
          $years[$year] = $year;
        }
      }
    }
    arsort($years);

    // Get the selected year, defaults to the first year in the dropdown.
    $year = key($years);
    if (($selectedYear = $selectedValues['year'] ?? NULL) && in_array($selectedYear, $years)) {
      $year = $selectedYear;
    }

    // Create a link to the visual.
    $visualPhase = $this->documentTypeToVisualPhase($documentType);
    if ($year && $visualPhase && !empty($this->values['fiscal_chapter_id'])) {
      $routeParams = [
        'jaar' => $year,
        'fase' => $visualPhase,
        'vuo' => 'ontvangsten',
        'hoofdstukMinfinId' => $this->values['fiscal_chapter_id'],
      ];
      if (!empty($this->values['fiscal_article_id'])) {
        $routeParams['artikelMinfinId'] = $this->values['fiscal_article_id'];
      }

      $quickLinks[] = Link::createFromRoute('Visual', 'minfin_visuals', $routeParams);
    }

    // Create a link to the chapter page.
    if ($year && !empty($this->values['fiscal_chapter_id'])) {
      $chapterId = $this->values['fiscal_chapter_id'];
      $title = $this->minfinNamingService->getHoofdstukName($year, $chapterId);
      if ($chapterLink = $this->minfinService->buildChapterLink($title, $chapterId, $year, $documentType ?? '')) {
        $quickLinks[] = $chapterLink;
      }
    }

    // Create download links for the open data types.
    if ($year && !empty($this->values['download_url'])) {
      $downloadUrls = [];
      foreach ($this->getMultivalue('download_url') as $url) {
        $values = explode('|', $url);
        if (isset($values[2])) {
          $downloadUrls[$values[2]][$values[0]] = $values[1];
        }
      }

      if (isset($downloadUrls[$year])) {
        foreach ($downloadUrls[$year] as $title => $uri) {
          try {
            $quickLinks[] = Link::fromTextAndUrl($title, Url::fromUri($uri));
          }
          catch (\Exception $e) {
            // Do nothing.
          }
        }
      }
    }

    return $quickLinks;
  }

}
