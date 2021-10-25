<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the verzelfstandigingen importer.
 */
class ImportVerzelfstandigingenForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_verzelfstandigingen_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'verzelfstandigingen';
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
    $type = (string) $formState->getValue('type');
    if ($type === 'agentschap' || $type === 'zbo') {
      return 6;
    }
    else {
      return 30;
    }
  }

  /**
   * Remove old data.
   *
   * @param int $jaar
   *   The jaar.
   * @param string $type
   *   The type.
   */
  protected function removeOldData(int $jaar, string $type): void {
    if ($type === 'toelichting_agentschap') {
      $this->connection->delete('mf_verzelfstandiging_uitleg')
        ->condition('type', 'agentschap', '=')
        ->execute();
    }
    elseif ($type === 'toelichting_zbo') {
      $this->connection->delete('mf_verzelfstandiging_uitleg')
        ->condition('type', 'zbo', '=')
        ->execute();
    }
    else {
      $this->connection->delete('mf_verzelfstandiging')
        ->condition('jaar', $jaar, '=')
        ->condition('type', $type, '=')
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $types = [
      'agentschap' => 'Agentschap',
      'toelichting_agentschap' => 'Agentschap (toelichting)',
      'zbo' => 'ZBO',
      'toelichting_zbo' => 'ZBO (toelichting)',
    ];
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#required' => TRUE,
      '#options' => $types,
      '#empty_option' => $this->t('- Select type -'),
      '#weight' => 20,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    $jaar = (int) $formState->getValue('year');
    $type = (string) $formState->getValue('type');

    // Delete all old data.
    $this->removeOldData($jaar, $type);

    $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv, 0, $csvSeparator);
      $lineNr = 1;
      while (($line = fgetcsv($csv, 0, $csvSeparator, '"')) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line);

        if ($type === 'toelichting_agentschap') {
          if (!$line[0]) {
            $args = ['@column' => 'A'];
            $message = "The following column(s) can't be empty: @column.";
            $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
            $this->rowSkipped++;
            continue;
          }

          try {
            $this->connection->insert('mf_verzelfstandiging_uitleg')
              ->fields([
                'type' => 'agentschap',
                'naam' => trim($line[0]),
                'afkorting' => $line[2] ?? '',
                'website' => $line[47] ?? '',
                'resource_identifier' => $line[56] ?? '',
                'fte' => $this->fixFloatValue($line[57], $lineNr, 'C'),
                'beschrijving' => $line[12] ?? '',
                'taken_en_bevoegdheden' => $line[60] ?? '',
                'rapport' => $line[63] ?? '',
                'rapport_titel' => $line[64] ?? '',
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
        elseif ($type === 'toelichting_zbo') {
          if (!$line[0]) {
            $args = ['@column' => 'A'];
            $message = "The following column(s) can't be empty: @column.";
            $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
            $this->rowSkipped++;
            continue;
          }

          try {
            $this->connection->insert('mf_verzelfstandiging_uitleg')
              ->fields([
                'type' => 'zbo',
                'naam' => trim($line[0]),
                'afkorting' => $line[2] ?? '',
                'ministerie' => $line[60] ?? '',
                'website' => $line[47] ?? '',
                'resource_identifier' => $line[56] ?? '',
                'fte' => $this->fixFloatValue($line[67], $lineNr, 'C'),
                'taken_en_bevoegdheden' => $line[62] ?? '',
                'evaluaties' => $line[73] ?? '',
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
        else {
          if ((int) $line[5] !== $jaar) {
            $args = ['%year' => $jaar, '@column' => 'F', '%value' => $line[5]];
            $message = "Year doesn't match. You're trying to import the data for %year, but the value in column @column is %value";
            $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
            $this->rowSkipped++;
            continue;
          }

          if (!$line[0] || !$line[1]) {
            $args = ['@column' => 'A & B'];
            $message = "The following column(s) can't be empty: @column.";
            $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
            $this->rowSkipped++;
            continue;
          }

          try {
            $this->connection->insert('mf_verzelfstandiging')
              ->fields([
                'jaar' => $jaar,
                'ministerie' => $line[0],
                'organisatie' => $line[1],
                'type' => $type,
                'link_jaarverslag' => $line[4] ?? '',
                'fte' => $this->fixFloatValue($line[2], $lineNr, 'C'),
                'bedrag' => $this->fixCurrencyValues($line[3], $lineNr, 'D'),
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
      }

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Clean up and checks the given value so that the output is a valid float.
   *
   * @param string|float|int $value
   *   The value that needs to be cleaned up.
   * @param int|null $lineNr
   *   If known the line on which the value was found.
   * @param string|null $column
   *   If known the column on which the value was found.
   *
   * @return float
   *   The cleaned up version of $value.
   */
  protected function fixFloatValue($value, int $lineNr = NULL, $column = NULL): float {
    $value = trim($value);
    if (empty($value)) {
      return (float) 0;
    }

    $returnValue = str_replace(',', '.', $value);
    if (!is_numeric($returnValue)) {
      $args = ['%value' => $value, '%return' => (float) 0];
      $message = "The value %value isn't a valid integer so the value has been changed to %return";
      if ($column) {
        $args['@column'] = $column;
        $message = "The value %value in column @column isn't a valid integer so the value has been changed to %return";
      }
      $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);
      return (float) 0;
    }

    return (float) $returnValue;
  }

}
