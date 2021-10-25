<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the budgettaire tabellen importer.
 */
class ImportBegrotingsstatenForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_begrotingsstaten_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'begrotingsstaten';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportFileTypes(): array {
    return ['csv'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $formState): void {
    // Empty for validation for now.
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    $jaar = (int) $formState->getValue('year');

    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv);
      $lineNr = 1;
      while (($line = fgetcsv($csv)) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line);

        if ((int) $line[0] !== $jaar) {
          $args = ['%year' => $jaar, '@column' => 'A', '%value' => $line[0]];
          $message = "Year doesn't match. You're trying to import the data for %year, but the value in column @column is %value";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if (!$line[1] || !$line[2] || !$line[3] || !$line[4] || !$line[5]) {
          $args = ['@column' => 'B, C, D, E, & F'];
          $message = "The following column(s) can't be empty: @column.";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        // Insert hoofdstuk.
        if (!$hoofdstukId = $this->insertHoofdstuk($line[2], $line[3], $jaar)) {
          continue;
        }

        // Insert artikel.
        if (!$artikelId = $this->insertArtikel($line[4], $line[2], $line[5], $jaar)) {
          continue;
        }

        // Insert begrotingsstaat.
        try {
          $keys = [
            'jaar' => $jaar,
            'vuo' => strtoupper($line[1]),
            'hoofdstuk_id' => $hoofdstukId,
            'artikel_id' => $artikelId,
          ];

          $fields = $keys;
          $fields['bedrag_begroting'] = $this->fixCurrencyValues($line[6], $lineNr, 'G');
          $fields['bedrag_vastgestelde_begroting'] = $this->fixCurrencyValues($line[7], $lineNr, 'H');
          $fields['bedrag_suppletoire1'] = $this->fixCurrencyValues($line[8], $lineNr, 'I');
          $fields['bedrag_suppletoire2'] = $this->fixCurrencyValues($line[9], $lineNr, 'J');
          $fields['bedrag_jaarverslag'] = $this->fixCurrencyValues($line[10], $lineNr, 'K');

          $this->connection->merge('mf_begrotingsstaat')
            ->keys($keys)
            ->fields($fields)
            ->execute();
          $this->rowsImported++;
        }
        catch (\Exception $e) {
          $this->logError(self::SEVERITY_ERROR, 'An unexpected error occurred while trying to import the this row.', [], $lineNr);
          $this->rowSkipped++;
          continue;
        }
      }

      return TRUE;
    }

    return FALSE;
  }

}
