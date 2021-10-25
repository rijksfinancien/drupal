<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the kamerstuk uitzonderingen importer.
 */
class ImportKamerstukUitzonderingenForm extends ImportKamerstukBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_uitzonderingen_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'uitzonderingen';
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
  protected function getColumnCount(FormStateInterface $formState): ?int {
    return 9;
  }

  /**
   * Remove old data.
   */
  protected function removeOldData(): void {
    $this->connection->truncate('mf_uitzonderingen')->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    unset($form['year']);
    unset($form['phase']);
    unset($form['phase_suffix']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {

    // Delete all old data.
    $this->removeOldData();

    $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv, 0, $csvSeparator);
      $lineNr = 1;
      while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
        $lineNr++;

        if (!(int) $line[0] || !$line[1] || !$line[2] || !$line[3]) {
          $args = ['@column' => 'A, B, C & D'];
          $message = "The following column(s) can't be empty: @column.";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        try {
          $this->connection->insert('mf_uitzonderingen')
            ->fields([
              'type' => $this->getRealType($line[1]),
              'jaar' => (int) $line[0],
              'fase' => $this->getRealPhase($line[1]),
              'hoofdstuk_minfin_id' => $line[2],
              'level_1' => $line[3],
              'level_2' => !empty($line[4]) ? $line[4] : NULL,
              'level_3' => !empty($line[5]) ? $line[5] : NULL,
              'hoofdstuk_alternatief_id' => !empty($line[6]) ? $line[6] : NULL,
              'artikel_minfin_id' => !empty($line[7]) ? $line[7] : NULL,
              'b_tabel' => !empty($line[8]) ? (int) $line[8] : NULL,
              'geen_subhoofdstukken' => !empty($line[9]) ? (int) $line[9] : NULL,
            ])
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
