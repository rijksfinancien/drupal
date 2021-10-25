<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the financiele instrumenten importer.
 */
class ImportFinancieleInstrumentenForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_financiele_instrumenten_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'financiele_instrumenten';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportFileTypes(): array {
    return ['csv'];
  }

  /**
   * Remove old data.
   *
   * @param int $jaar
   *   Jaar.
   * @param string $hoofdstukMinfinId
   *   Hoofdstuk Minfin id.
   */
  protected function removeOldData(int $jaar, string $hoofdstukMinfinId): void {
    $this->connection->delete('mf_financiele_instrumenten')
      ->condition('jaar', $jaar, '=')
      ->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $form['sub_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chapter'),
      '#required' => TRUE,
      '#size' => 5,
    ];

    return $form;
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
    $hoofdstukMinfinId = $formState->getValue('sub_type');

    // Delete all old data.
    $this->removeOldData($jaar, $hoofdstukMinfinId);

    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv);
      $lineNr = 1;
      while (($line = fgetcsv($csv)) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line);

        // Ron some cleanup function to make the data more consistent.
        $line[3] = $this->fixArtikel($line[3], $lineNr);
        if (substr_count($line[9], '.') === 1) {
          $explode = explode('.', $line[9]);
          $line[9] = $explode[0] . substr($explode[1], 0, 3);
        }
        $line[9] = $this->fixCurrencyValues($line[9], $lineNr, 'J');

        if ((int) $line[0] !== $jaar) {
          $args = ['%year' => $jaar, '@column' => 'A', '%value' => $line[0]];
          $message = "Year doesn't match. You're trying to import the data for %year, but the value in column @column is %value";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if ($line[1] !== $hoofdstukMinfinId) {
          $args = ['%hoofdstukMinfinId' => $hoofdstukMinfinId, '@column' => 'A', '%value' => $line[1]];
          $message = "Chapter doesn't match. You're trying to import the data for %hoofdstukMinfinId, but the value in column @column is %value";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if (empty($line[8]) && empty($line[9])) {
          $args = ['@column' => 'I & J'];
          $message = 'The combination of column @columns cannot be empty, at least 1 column must be filled.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        try {
          $fields = [
            'jaar' => $line[0],
            'hoofdstuk_minfin_id' => $hoofdstukMinfinId,
            'artikel_minfin_id' => $line[3],
            'instrument' => $line[6],
            'regeling' => $line[7],
            'ontvanger' => $line[8],
          ];
          if (!is_null($line[9])) {
            $fields['bedrag'] = $line[9] * 1000;
          }

          // Insert the record into the database.
          $this->connection->insert('mf_financiele_instrumenten')
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
