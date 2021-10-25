<?php

/**
 * @file
 * Handles the batch callbacks.
 */

/**
 * Re-index a single item.
 *
 * @param bool $appendix
 *   If this kamerstuk is an appendix or not.
 * @param int $id
 *   The kamerstuk (bijlagen) id.
 * @param array &$context
 *   The batch API context.
 */
function minfin_kamerstuk_reindex_batch_run(bool $appendix, int $id, array &$context) {
  /** @var \Drupal\minfin\SolrKamerstukClientInterface $solrKamerstuk */
  $solrKamerstuk = \Drupal::service('minfin.solr_kamerstuk');

  if ($appendix) {
    $query = \Drupal::database()->select('mf_kamerstuk_bijlage', 'k');
    $query->condition('k.kamerstuk_bijlage_id', $id, '=');
  }
  else {
    $query = \Drupal::database()->select('mf_kamerstuk', 'k');
    $query->condition('k.kamerstuk_id', $id, '=');
  }
  $query->addField('k', 'type', 'type');
  $query->addField('k', 'fase', 'phase');
  $query->addField('k', 'jaar', 'year');
  $query->addField('k', 'naam', 'name');
  $query->addField('k', 'html', 'html');
  $query->addField('k', 'anchor', 'anchor');
  $query->addField('k', 'hoofdstuk_minfin_id', 'hoofdstukMinfinId');
  $query->addField('k', 'artikel_minfin_id', 'artikelMinfinId');
  $result = $query->execute();
  while ($record = $result->fetchAssoc()) {
    if (!empty($record['html']) && !empty($record['anchor'])) {
      $solrKamerstuk->update($appendix, $record['type'], $record['phase'], (int) $record['year'], $record['name'], $record['html'], $record['anchor'], $record['hoofdstukMinfinId'] ?? NULL, $record['artikelMinfinId'] ?? NULL);
    }
  }

  $context['results'][] = $id;
}

/**
 * Batch 'finished' callback.
 */
function minfin_kamerstuk_reindex_batch_finished($success, $results, $operations) {
  \Drupal::messenger()->addStatus(t('Finished indexing @count items', ['@count' => count($results)]));
}
