<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Archiver\ArchiverException;
use Drupal\Core\Archiver\Zip;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * The importer for 'kamerstuk'.
 *
 * @package Drupal\minfin\Form
 */
class ImportKamerstukForm extends ImportKamerstukBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_kamerstuk_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'kamerstuk';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportFileTypes(): array {
    return ['html', 'csv', 'zip'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getColumnCount(FormStateInterface $formState): ?int {
    return 5;
  }

  /**
   * Remove old data.
   *
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param bool $appendix
   *   If this kamerstuk is an appendix or not.
   * @param string $type
   *   The type.
   * @param string|null $hoofdstukMinfinId
   *   The chapter minfin id.
   */
  protected function removeOldData(int $year, string $phase, bool $appendix, string $type, ?string $hoofdstukMinfinId): void {
    $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
    $tableId = $appendix ? 'kamerstuk_bijlage_id' : 'kamerstuk_id';

    $query = $this->connection->select($table, 'k');
    $query->addField('k', $tableId, 'kamerstuk_id');
    $query->addField('k', 'anchor', 'anchor');
    $query->condition('jaar', $year);
    $query->condition('fase', $phase);
    $query->condition('type', $type);
    if ($hoofdstukMinfinId) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId);
    }
    else {
      $query->isNull('hoofdstuk_minfin_id');
    }
    $results = $query->execute()->fetchAllKeyed();

    foreach ($results as $kamerstukId => $anchor) {
      $this->connection->delete($table)
        ->condition($tableId, $kamerstukId, '=')
        ->execute();

      // Check if we've got some files that need to be cleanup up.
      $query = $this->connection->select('file_usage');
      $query->fields('file_usage', ['fid']);
      $query->condition('module', 'minfin', '=');
      $query->condition('type', 'kamerstuk', '=');
      $query->condition('id', $kamerstukId, '=');
      $result = $query->execute();
      if (($fid = $result->fetchField()) && $file = $this->fileStorage->load($fid)) {
        /** @var \Drupal\file\FileInterface $file */
        $this->fileUsage->delete($file, 'minfin', 'kamerstuk', $kamerstukId);
      }

      // Remove the kamerstuk from the SOLR index.
      $this->solrKamersukClient->delete($type, $phase, $year, $anchor, $hoofdstukMinfinId);
    }

    $this->connection->delete('mf_kamerstuk_dossier')
      ->condition('type', $type, '=')
      ->condition('jaar', $year, '=')
      ->condition('fase', $phase, '=')
      ->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $form['import_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Import type'),
      '#options' => [
        'html' => $this->t('HTML'),
        'csv' => $this->t('CSV'),
        'zip' => $this->t('Zip (DAO)'),
      ],
      '#weight' => 1,
    ];

    // @todo this should probably be required like phase and year
    $form['dossier'] = [
      '#type' => 'number',
      '#title' => 'Dossiernummer',
      '#weight' => 2,
      '#states' => [
        'visible' => [
          'select[name="import_type"]' => [
            ['value' => 'html'],
            ['value' => 'zip'],
          ],
        ],
      ],
    ];

    foreach (['year', 'phase', 'phase_suffix', 'hoofdstuk_minfin_id'] as $field) {
      $form[$field]['#required'] = FALSE;
      $form[$field]['#states']['visible']['select[name="import_type"]'] = [
        ['value' => 'html'],
        ['value' => 'zip'],
      ];
      $form[$field]['#states']['visible']['select[name="import_type"]'] = [
        ['value' => 'html'],
        ['value' => 'zip'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    $importType = $formState->getValue('import_type');
    $fileUri = $file->getFileUri();
    $fileName = $file->getFilename();

    // HTML import.
    if ($importType === 'html') {
      $year = (int) $formState->getValue('year');
      $importPhase = $formState->getValue('phase');
      if (in_array($importPhase, ['isb (mvt)', 'isb (wet)'])) {
        $importPhase = $formState->getValue('phase_suffix') . 'e ' . $importPhase;
      }
      $phase = $this->getRealPhase($importPhase);

      $hoofdstukMinfinId = $formState->getValue('hoofdstuk_minfin_id');
      if (!$hoofdstukMinfinId) {
        $hoofdstukMinfinId = $this->extractHoofdstukMinfinId($fileName);
      }

      $dossierNumber = $formState->getValue('dossier');
      if (!$dossierNumber) {
        $explode = explode('-', $fileName);
        $dossierNumber = $explode[2] ?? NULL;
      }

      $this->importFile($fileUri, $year, $phase, $importPhase, $importType, $hoofdstukMinfinId, (int) $dossierNumber);
    }

    // CSV import.
    elseif ($importType === 'csv') {
      $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
      if ($csv = fopen($file->getFileUri(), 'rb')) {
        // Skip the first line and then start looping over the following lines.
        fgetcsv($csv, 0, $csvSeparator);
        $lineNr = 1;
        while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
          $lineNr++;
          if (isset($line[0], $line[1], $line[2], $line[4])) {
            $year = (int) $line[1];
            $phase = $this->getRealPhase($line[2]);

            $this->importFile($line[4], $year, $phase, $line[2], $importType, $line[3] ?? NULL, (int) $line[0]);
          }
        }
      }
    }

    // Zip import.
    elseif ($importType === 'zip') {
      try {
        $year = (int) $formState->getValue('year');
        $importPhase = $formState->getValue('phase');
        if (in_array($importPhase, ['isb (mvt)', 'isb (wet)'])) {
          $importPhase = $formState->getValue('phase_suffix') . 'e ' . $importPhase;
        }
        $phase = $this->getRealPhase($importPhase);

        $zip = new Zip($this->fileSystem->realpath($file->getFileUri()));
        $zipDir = '/tmp/minfin_extracted_zip';
        $zip->extract($zipDir);
        foreach ($zip->listContents() as $fileName) {
          $ext = pathinfo($fileName, PATHINFO_EXTENSION);
          if ($ext === 'html') {

            $hoofdstukMinfinId = $formState->getValue('hoofdstuk_minfin_id');
            if (!$hoofdstukMinfinId) {
              $hoofdstukMinfinId = $this->extractHoofdstukMinfinId($fileName);
            }
            $this->importFile($zipDir . '/' . $fileName, $year, $phase, $importPhase, $importType, $hoofdstukMinfinId, (int) $formState->getValue('dossier'));
          }
        }
        array_map('unlink', glob($zipDir . '/*.*'));
      }
      catch (ArchiverException $exception) {
        $this->messenger()->addError($this->t('Failed to unzip file.'));
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Imports the data of the given file.
   *
   * @param string $fileUri
   *   The file uri.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $importPhase
   *   The import phase.
   * @param string $importType
   *   The import type.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param int|null $dossierNumber
   *   The dossier number.
   */
  protected function importFile(string $fileUri, int $year, string $phase, string $importPhase, string $importType, ?string $hoofdstukMinfinId, ?int $dossierNumber): void {
    $appendix = $this->isKamerstukAppendix($importPhase);
    $type = $this->getRealType($importPhase);

    // Cleanup kamerstukken.
    $this->removeOldData($year, $phase, $appendix, $type, $hoofdstukMinfinId);

    if ($dossierNumber) {
      $this->connection->merge('mf_kamerstuk_dossier')
        ->keys([
          'type' => $type,
          'jaar' => $year,
          'fase' => $phase,
          'hoofdstuk_minfin_id' => $hoofdstukMinfinId,
        ])
        ->fields([
          'dossier_number' => $dossierNumber,
        ])
        ->execute();
    }

    $html = $this->getKamerstukHtml($importType, $fileUri);
    if (!$html) {
      $this->rowSkipped++;
      return;
    }

    // If its an ISB get the custom title from the meta tags.
    if (in_array($type, ['isb_memorie_van_toelichting', 'isb_voorstel_van_wet']) && $hoofdstukMinfinId) {
      $this->setIsbTitle($fileUri, $year, $phase, $hoofdstukMinfinId);
    }

    $dom = $this->newDomDocument($html);
    $xpath = new \DOMXPath($dom);

    if ($type === 'voorstel_van_wet' || $type === 'isb_voorstel_van_wet') {
      if ($element = $xpath->query('//div[@class="stuk"]')->item(0)) {
        foreach ($xpath->query('//div[@class="stuk"]//h1[@class="stuktitel"]') ?? [] as $child) {
          $element->removeChild($child);
        }
        foreach ($xpath->query('//div[@class="stuk"]//p[@class="kamerstukdatum"]') ?? [] as $child) {
          $element->removeChild($child);
        }

        $html = $dom->saveHTML($element) ?? NULL;
        $this->createKamerstuk($appendix, 'voorstel-van-wet', 'Voorstel van Wet', $html, $type, $year, $phase, $importPhase, $hoofdstukMinfinId, $this->getArtikelMinfinId($type, trim($element->nodeValue)), 1);
      }
    }
    else {
      if ($salutation = $xpath->query('//div[@class="voorstel-wet"]//div[@class="aanhef"]')->item(0)) {
        $this->createKamerstuk($appendix, 'aanhef', 'Aanhef', $dom->saveHTML($salutation), $type, $year, $phase, $importPhase, $hoofdstukMinfinId, $this->getArtikelMinfinId($type, trim($salutation->nodeValue)), 0);
      }

      // Try and import the visual summary. We do this by importing a block
      // that only contains divs with images.
      if ($type === 'miljoenennota') {
        foreach ($xpath->query('//div[@class="algemeen"]') ?? [] as $element) {
          $summary = TRUE;
          foreach ($element->childNodes as $node) {
            if (!(($node instanceof \DOMText) || ($node instanceof \DOMElement && (($node->tagName === 'div' && $node->getAttribute('class') === 'plaatje') || ($node->tagName === 'a'))))) {
              $summary = FALSE;
              continue;
            }
          }
          if ($summary) {
            $this->createKamerstuk($appendix, 'summary', 'Visuele samenvatting', $dom->saveHTML($element), $type, $year, $phase, $importPhase, NULL, NULL, 0);
          }
        }
      }

      $level = -1;
      $startTag = $this->getStartTag($html);
      foreach ($xpath->query('//' . $startTag) ?? [] as $element) {
        if ($element->getAttribute('class') !== 'officiele-inhoudsopgave_kop') {
          $this->importRecursive($appendix, $dom, $xpath, $element, $type, $year, $phase, $importPhase, $hoofdstukMinfinId, ++$level, NULL, NULL, $startTag, []);
        }
      }
    }

    $this->importVoetstuk($dom, $xpath, $type, $year, $phase, $hoofdstukMinfinId);

    // SOLR errors.
    if ($errors = $this->solrKamersukClient->getErrors()) {
      $this->messenger()->addError($this->t('SOLR request has failed with errors.<br>%errors', [
        '%errors' => implode('<br>', $errors),
      ]));
      return;
    }
  }

  /**
   * Extract the hoofdstuk minfin id from file name.
   *
   * @param string $fileName
   *   The file name.
   *
   * @return null|string
   *   The hoofdstuk minfin id.
   */
  protected function extractHoofdstukMinfinId(string $fileName): ?string {
    $explode = explode('-', $fileName);
    $count = count($explode);
    if ($count === 4) {
      return $explode[$count - 2] ?? NULL;
    }
    return NULL;
  }

  /**
   * Extract the type from file name.
   *
   * @param string $fileName
   *   The file name.
   * @param string $phase
   *   The phase.
   *
   * @return false|string
   *   The type.
   */
  protected function extractType(string $fileName, string $phase) {
    $fileName = str_replace('.html', '', $fileName);
    $explode = explode('-', $fileName);

    // Example: https://zoek.officielebekendmakingen.nl/kst-x-1.html.
    if (count($explode) === 3) {
      if ($explode[0] === 'stb') {
        return 'belastingplan_staatsblad';
      }

      if ((int) $explode[2] === 1) {
        switch ($phase) {
          case 'OWB':
            return 'miljoenennota';

          case 'JV':
            return 'financieel_jaarverslag';

          case '1SUPP':
            return 'voorjaarsnota';

          case '2SUPP':
            return 'najaarsnota';

          default:
            return 'undefined';
        }
      }

      if ((int) $explode[2] === 2) {
        return 'belastingplan_voorstel_van_wet';
      }

      if ((int) $explode[2] === 3) {
        return 'belastingplan_memorie_van_toelichting';
      }
    }

    // Example: https://zoek.officielebekendmakingen.nl/kst-x-y-i.html.
    if (count($explode) === 4) {
      $number = (int) $explode[3];

      if ($phase === 'JV') {
        switch ($number) {
          case 1:
            return 'jaarverslag';

          case 2:
            return 'brief_algemene_rekenkamer';

          case 3:
            return 'voorstel_van_wet';

          case 4:
            return 'memorie_van_toelichting';

          default:
            return 'undefined';
        }
      }

      switch ($number) {
        case 1:
          return 'voorstel_van_wet';

        case 2:
          return 'memorie_van_toelichting';

        default:
          return 'undefined';
      }

    }

    return 'undefined';
  }

  /**
   * Helper function to set the ISB title.
   *
   * @param string $fileUri
   *   The file uri.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   */
  protected function setIsbTitle(string $fileUri, int $year, string $phase, string $hoofdstukMinfinId): void {
    $metaTags = get_meta_tags($fileUri);
    if (!empty($metaTags['overheidop_dossiertitel'])) {
      $fields['naam'] = $metaTags['overheidop_dossiertitel'];
      preg_match_all('#\((.*?)\)#', $fields['naam'], $match);
      if ($match[1]) {
        $fields['naam'] = last($match[1]);
      }

      if (!empty($metaTags['dcterms_available'])) {
        $fields['date'] = date('Y-m-d\TH:i:s\Z', strtotime($metaTags['dcterms_available']));
      }

      $this->connection->merge('mf_kamerstuk_isb_title')
        ->keys([
          'jaar' => $year,
          'fase' => $phase,
          'hoofdstuk_minfin_id' => $hoofdstukMinfinId,
        ])
        ->fields($fields)
        ->execute();
    }
  }

  /**
   * Extract the anchor href attribute from the given element.
   *
   * @param \DOMElement $element
   *   The DOM element.
   *
   * @return string|null
   *   The anchor href attribute.
   */
  protected function extractAnchorHref(\DOMElement $element): ?string {
    return $element->attributes->getNamedItem('name')->nodeValue;
  }

  /**
   * Extract the HTML for the given element.
   *
   * @param \DOMDocument $dom
   *   The DOM.
   * @param \DOMXPath $xpath
   *   The xpath.
   * @param \DOMElement $element
   *   The DOM element.
   * @param string $startTag
   *   The start tag.
   * @param bool $importSubchapters
   *   A boolean indicating whether to import subchapters or not.
   *
   * @return array
   *   The extracted HTML.
   */
  protected function extractHtml(\DOMDocument $dom, \DOMXPath $xpath, \DOMElement $element, string $startTag, bool $importSubchapters = TRUE): array {
    $data = [
      'html' => NULL,
    ];

    // Determine sibling tags.
    $siblingTags = ['h3', 'h4'];
    if ($startTag === 'h3') {
      $siblingTags = ['h4', 'h5'];
    }

    if (!$importSubchapters) {
      array_pop($siblingTags);
    }

    foreach ($xpath->query($element->getNodePath() . '/following-sibling::*') as $sibling) {
      /** @var \DOMElement $sibling */
      if ($sibling->tagName === $startTag) {
        $data['stop'] = TRUE;
        break;
      }

      if (in_array($sibling->tagName, $siblingTags, TRUE)) {
        $data['next_element'] = $sibling;
        break;
      }

      $data['html'] .= $dom->saveHTML($sibling);
    }
    return $data;
  }

  /**
   * Extract the Title for the given element.
   *
   * @param \DOMElement $element
   *   The DOM element.
   *
   * @return string
   *   The extracted Title.
   */
  protected function extractTitle(\DOMElement $element): string {
    $title = '';
    foreach ($element->childNodes as $child) {
      if ($child instanceof \DOMElement) {
        if ($child->tagName === 'a' && !empty($child->getAttribute('class'))) {
          $title .= $child->ownerDocument->saveXML($child);
        }
      }
      else {
        $title .= $child->ownerDocument->saveXML($child);
      }
    }

    return trim($title);
  }

  /**
   * Helper function to extract the artikel minfin id from the given title.
   *
   * @param string $type
   *   The type.
   * @param string $title
   *   The title.
   *
   * @return int|null
   *   The artikel minfin id.
   */
  protected function getArtikelMinfinId(string $type, string $title): ?int {
    if ($type === 'voorstel_van_wet') {
      return NULL;
    }

    $explode = explode(' ', $title);
    if (isset($explode[0], $explode[1])) {
      if (strtolower($explode[0]) === 'artikel') {
        return (int) $explode[1];
      }

      if (strtolower($explode[1]) === 'artikel') {
        return (int) $explode[2];
      }

      if (strtolower($explode[0]) === 'art.nr.') {
        return (int) $explode[1];
      }

      if (strtolower($explode[1]) === 'art.nr.') {
        return (int) $explode[2];
      }

      if (strtolower($explode[0]) === 'beleidsartikel') {
        return (int) $explode[1];
      }

      if (strtolower($explode[1]) === 'beleidsartikel') {
        return (int) $explode[2];
      }

      if (strtolower($explode[0]) === 'niet-beleidsartikel') {
        return (int) $explode[1];
      }

      if (strtolower($explode[1]) === 'niet-beleidsartikel') {
        return (int) $explode[2];
      }
    }

    return NULL;
  }

  /**
   * Get the kamerstuk html.
   *
   * @param string $importType
   *   The import type.
   * @param string $fileUri
   *   The file uri.
   *
   * @return null|string
   *   The html.
   */
  protected function getKamerstukHtml(string $importType, string $fileUri): ?string {
    $html = NULL;
    if ($importType === 'csv') {
      try {
        $response = $this->httpClient->request('GET', $fileUri);
        if ($response->getStatusCode() === 200) {
          $html = $response->getBody()->getContents();
        }
      }
      catch (RequestException | GuzzleException $e) {
        $this->logError(self::SEVERITY_ERROR, 'Failed to load html from source.');
        return NULL;
      }
    }
    else {
      $html = file_get_contents($fileUri);
    }

    if (!$html) {
      $this->logError(self::SEVERITY_ERROR, 'Failed to load html from source.');
      return NULL;
    }

    // Remove the html comments from the html input.
    $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
    // Strip out a certain malformed html comment that ends up on the screen.
    $html = preg_replace('/<\?(.|\s)*?\?>/', '', $html);
    // Strip unnecessary white spaces.
    $html = preg_replace('/\s+/', ' ', (string) $html);

    $dom = $this->newDomDocument($html);
    $xpath = new \DOMXPath($dom);

    // Determine the main wrapper based on the import type.
    $mainWrapper = '//main';
    if ($importType === 'zip') {
      $mainWrapper = '//div[@id="broodtekst"]';
    }

    // Extract the main kamerstuk wrapper from the html.
    $main = $xpath->query($mainWrapper)->item(0);
    if (!$main) {
      $this->logError(self::SEVERITY_ERROR, 'Failed to load main wrapper.');
      return NULL;
    }
    $html = $dom->saveHtml($main);

    // Remove the Table of Content from the html as we don't import it anyway.
    foreach ($xpath->query($mainWrapper . '//div[@class="officiele-inhoudsopgave"]') ?? [] as $child) {
      $html = str_replace($dom->saveHTML($child), '', $html);
    }

    return $html;
  }

  /**
   * Get the start tag for the kamerstuk.
   *
   * @param string $html
   *   The kamerstuk html.
   *
   * @return string
   *   The starting tag.
   */
  protected function getSTartTag(string $html): string {
    $h2 = strpos($html, '<h2');
    $h3 = strpos($html, '<h3');

    if (($h2 && $h3) && ($h3 < $h2)) {
      return 'h3';
    }

    if (!$h2 && $h3) {
      return 'h3';
    }

    return 'h2';
  }

  /**
   * Helper function to import HTML recursively.
   *
   * @param bool $appendix
   *   If this kamerstuk is an appendix or not.
   * @param \DOMDocument $dom
   *   The DOM.
   * @param \DOMXPath $xpath
   *   The xpath.
   * @param \DOMElement $element
   *   The DOM element.
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $importPhase
   *   The import phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param int|null $level1
   *   The first level.
   * @param int|null $level2
   *   The second level.
   * @param int|null $level3
   *   The third level.
   * @param string $startTag
   *   The start tag.
   * @param array $values
   *   The values.
   */
  protected function importRecursive(bool $appendix, \DOMDocument $dom, \DOMXPath $xpath, \DOMElement $element, string $type, int $year, string $phase, string $importPhase, ?string $hoofdstukMinfinId, ?int $level1, ?int $level2, ?int $level3, string $startTag, array $values = []): void {
    if ($startTag === 'h2') {
      switch ($element->tagName ?? NULL) {
        case 'h3':
          $level2++;
          $level3 = NULL;
          break;

        case 'h4':
          $level3++;
          break;

        case 'h2':
        default:
          $level1++;
          $level2 = NULL;
          $level3 = NULL;
      }
    }

    if ($startTag === 'h3') {
      switch ($element->tagName ?? NULL) {
        case 'h4':
          $level2++;
          $level3 = NULL;
          break;

        case 'h5':
          $level3++;
          break;

        case 'h3':
        default:
          $level1++;
          $level2 = NULL;
          $level3 = NULL;
      }
    }

    $importSubchapters = TRUE;
    if ($exception = $this->getImportException($type, $year, $phase, $hoofdstukMinfinId, $level1, $level2, $level3)) {
      if ($exception['geen_subhoofdstukken'] ?? NULL) {
        $importSubchapters = FALSE;
      }
    }

    $data = $this->extractHtml($dom, $xpath, $element, $startTag, $importSubchapters);

    /** @var \DOMElement $anchorElement */
    if (!$anchorElement = $xpath->query($element->getNodePath() . '/a')->item(0)) {
      return;
    }

    $this->createKamerstuk($appendix, $this->extractAnchorHref($anchorElement), $this->extractTitle($element), $data['html'] ?? NULL, $type, $year, $phase, $importPhase, $hoofdstukMinfinId, $this->getArtikelMinfinId($type, trim($element->nodeValue)), $level1, $level2, $level3);

    // Import children.
    if (!($data['stop'] ?? FALSE) && $nextElement = $data['next_element'] ?? NULL) {
      $this->importRecursive($appendix, $dom, $xpath, $nextElement, $type, $year, $phase, $importPhase, $hoofdstukMinfinId, $level1, $level2, $level3, $startTag, $values);
    }
  }

  /**
   * Transfer the images found in the html to Drupal's file storage.
   *
   * @param string $html
   *   The html.
   * @param int $kamerstukId
   *   The kamerstuk id.
   * @param bool $appendix
   *   If this kamerstuk is an appendix or not.
   */
  protected function importImages(string $html, int $kamerstukId, $appendix): void {
    $dom = $this->newDomDocument($html);
    $images = $dom->getElementsByTagName('img');

    // Only continue if we actually have an image to transfer.
    if (count($images)) {
      /** @var \DOMElement $image */
      foreach ($images as $image) {
        $src = explode('/', $image->getAttribute('src'));
        $imageName = end($src);

        /** @var \Drupal\file\Entity\File $file */
        try {
          $file = NULL;
          // Check if the file is on our local system.
          if (file_exists('/tmp/minfin_extracted_zip/' . $imageName)) {
            $file = file_save_data(file_get_contents('/tmp/minfin_extracted_zip/' . $imageName), 'public://kamerstuk/dao/' . $imageName);
          }
          // Check if we can get the file from zoek.officielebekendmakingen.nl.
          else {
            $file = system_retrieve_file('https://zoek.officielebekendmakingen.nl/' . $imageName, 'public://kamerstuk/' . $imageName, TRUE);
          }

          if ($file) {
            $this->fileUsage->add($file, 'minfin', 'kamerstuk', $kamerstukId);
            $file->save();
            $image->setAttribute('src', file_url_transform_relative(file_create_url($file->getFileUri())));
          }
          else {
            $missingFile = file_url_transform_relative(file_create_url('public://kamerstuk/missing-image/' . $imageName));
            $image->setAttribute('src', $missingFile);
            $args = [':image' => $imageName, ':path' => $missingFile];
            $message = 'Failed to import image: :image, you can manually fix this image by uploading it to :path.';
            $this->logError(self::SEVERITY_WARNING, $message, $args);
          }
        }
        catch (EntityStorageException $e) {
          $args = [':image' => $imageName];
          $message = 'Failed to import image: :image.';
          $this->logError(self::SEVERITY_WARNING, $message, $args);
        }
      }

      $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
      $tableId = $appendix ? 'kamerstuk_bijlage_id' : 'kamerstuk_id';
      $this->connection->update($table)
        ->fields(['html' => substr($dom->saveHTML(), 12, -15)])
        ->condition($tableId, $kamerstukId, '=')
        ->execute();
    }
  }

  /**
   * Import the voetstuk.
   *
   * @param \DOMDocument $dom
   *   The DOM.
   * @param \DOMXPath $xpath
   *   The xpath.
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   */
  protected function importVoetstuk(\DOMDocument $dom, \DOMXPath $xpath, string $type, int $year, string $phase, ?string $hoofdstukMinfinId): void {
    /** @var \DOMElement $note */
    foreach ($xpath->query('//div[@id="noten"]//div') ?? [] as $note) {
      if ($id = $note->attributes->getNamedItem('id')->nodeValue) {
        $minfinVoetstukId = str_replace('supernote-note-ID-', '', $id);

        // Cleanup voetstuk.
        $query = $this->connection->select('mf_voetstuk');
        $query->fields('mf_voetstuk', ['voetstuk_id']);
        $query->condition('minfin_voetstuk_id', $minfinVoetstukId);
        $query->condition('type', $type);
        $query->condition('jaar', $year);
        $query->condition('fase', $phase);
        if ($hoofdstukMinfinId) {
          $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId);
        }
        else {
          $query->isNull('hoofdstuk_minfin_id');
        }
        if ($data = $query->execute()->fetchCol()) {
          $this->connection->delete('mf_voetstuk')
            ->condition('voetstuk_id', (int) reset($data), '=')
            ->execute();
        }

        $fields = [
          'minfin_voetstuk_id' => $minfinVoetstukId,
          'type' => $type,
          'jaar' => $year,
          'fase' => $phase,
          'hoofdstuk_minfin_id' => $hoofdstukMinfinId,
          'html' => $dom->saveHTML($note),
        ];
        if ($hoofdstukMinfinId) {
          $fields['hoofdstuk_minfin_id'] = $hoofdstukMinfinId;
        }

        // Insert in database.
        $this->connection->insert('mf_voetstuk')
          ->fields($fields)
          ->execute();
      }
    }
  }

  /**
   * Helper function to import HTML recursively.
   *
   * @param bool $appendix
   *   If this kamerstuk is an appendix or not.
   * @param string|null $anchor
   *   The anchor.
   * @param string $name
   *   The name.
   * @param string|null $html
   *   The HTML.
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string $importPhase
   *   The import phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $artikelMinfinId
   *   The artikel minfin id.
   * @param int|null $level1
   *   The first level.
   * @param int|null $level2
   *   The second level.
   * @param int|null $level3
   *   The third level.
   */
  protected function createKamerstuk(bool $appendix, ?string $anchor, string $name, ?string $html, string $type, int $year, string $phase, string $importPhase, ?string $hoofdstukMinfinId, ?string $artikelMinfinId, ?int $level1 = NULL, ?int $level2 = NULL, ?int $level3 = NULL): void {
    $fields = [
      'naam' => $name,
      'html' => $html,
      'empty_record' => (int) empty($html),
      'type' => $type,
      'jaar' => $year,
      'fase' => $phase,
      'import_fase' => $importPhase,
    ];
    if ($anchor) {
      $fields['anchor'] = $anchor;
    }
    if ($hoofdstukMinfinId) {
      $fields['hoofdstuk_minfin_id'] = $hoofdstukMinfinId;
    }
    if ($artikelMinfinId) {
      $fields['artikel_minfin_id'] = $artikelMinfinId;
    }
    if ($level1 !== NULL) {
      $fields['level_1'] = $level1;
    }
    if ($level2) {
      $fields['level_2'] = $level2;
    }
    if ($level3) {
      $fields['level_3'] = $level3;
    }

    // Get potential import exception.
    if ($exception = $this->getImportException($type, $year, $phase, $hoofdstukMinfinId, $level1, $level2, $level3)) {
      foreach (['hoofdstuk_alternatief_id', 'artikel_minfin_id', 'b_tabel'] as $field) {
        if (!empty($exception[$field])) {
          $fields[$field] = $exception[$field];
        }
      }
    }

    $table = $appendix ? 'mf_kamerstuk_bijlage' : 'mf_kamerstuk';
    $kamerstukId = $this->connection->insert($table)->fields($fields)->execute();
    if (!$kamerstukId) {
      $this->rowSkipped++;
      return;
    }

    if (!empty($html)) {
      $this->importImages($html, $kamerstukId, $appendix);

      // Index in SOLR.
      if ($anchor !== NULL) {
        $this->solrKamersukClient->update($appendix, $type, $phase, $year, $name, $html, $anchor, $hoofdstukMinfinId, $artikelMinfinId);
      }
    }

    $this->rowsImported++;
  }

  /**
   * Retrieve import exception for the given data.
   *
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string $phase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The minfin chapter id.
   * @param int|null $level1
   *   The first level.
   * @param int|null $level2
   *   The second level.
   * @param int|null $level3
   *   The third level.
   *
   * @return array
   *   The import exception.
   */
  protected function getImportException(string $type, int $year, string $phase, ?string $hoofdstukMinfinId, ?int $level1 = NULL, ?int $level2 = NULL, ?int $level3 = NULL): array {
    $query = $this->connection->select('mf_uitzonderingen', 'mf_uitzonderingen');
    $query->fields('mf_uitzonderingen');
    $query->condition('type', $type);
    $query->condition('jaar', $year);
    $query->condition('fase', $phase);

    if ($hoofdstukMinfinId) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId);
    }
    else {
      $query->isNull('hoofdstuk_minfin_id');
    }

    if ($level1) {
      $query->condition('level_1', $level1);
    }
    else {
      $query->isNull('level_1');
    }

    if ($level2) {
      $query->condition('level_2', $level2);
    }
    else {
      $query->isNull('level_2');
    }

    if ($level3) {
      $query->condition('level_3', $level3);
    }
    else {
      $query->isNull('level_3');
    }

    if ($result = $query->execute()) {
      if ($data = $result->fetchAssoc()) {
        return $data;
      }
    }
    return [];
  }

  /**
   * Returns a DOMDocument element with the html loaded.
   *
   * @param string $html
   *   The html to load.
   *
   * @return \DOMDocument
   *   The DOMDocument element.
   */
  protected function newDomDocument(string $html): \DOMDocument {
    libxml_use_internal_errors(TRUE);
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    $dom = new \DOMDocument();
    $dom->loadHTML($html, LIBXML_HTML_NODEFDTD);
    return $dom;
  }

}
