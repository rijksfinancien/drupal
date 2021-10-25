<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the subsidies importer.
 */
class ImportSubsidiesForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_subsidies_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'subsidies';
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

        // Insert the subsidies.
        try {
          $fields = [
            'jaar' => $jaar,
            'hoofdstuk_minfin_id' => $line[1],
            'beleid' => $line[4],
            'regeling' => $line[5],
            'ontvanger' => $line[6],
            'bedrag' => $this->fixCurrencyValues($line[8], $lineNr, 'I'),
          ];

          if (!empty($line[3]) && ($artikelId = $this->fixArtikel($line[3], $lineNr))) {
            $fields['artikel_minfin_id'] = $artikelId;
          }

          $this->connection->insert('mf_subsidie')
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
