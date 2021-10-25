<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the fiscale regelingen importer.
 */
class ImportFiscaleRegelingenForm extends ImportBaseForm {

  /**
   * Defines the begroting import type.
   */
  protected const IMPORT_TYPE_BEGROTING = 'begroting';

  /**
   * Defines the fiscaal import type.
   */
  protected const IMPORT_TYPE_FISCAAL = 'fiscaal';

  /**
   * Defines the premie import type.
   */
  protected const IMPORT_TYPE_PREMIE = 'premie';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_fiscale_regelingen_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'fiscale_regelingen';
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
   *   Year.
   * @param string $type
   *   Type.
   */
  protected function removeOldData(int $jaar, string $type): void {
    $field = 'bedrag_begroting';
    if ($type === self::IMPORT_TYPE_FISCAAL) {
      $field = 'bedrag_fiscaal';
    }
    elseif ($type === self::IMPORT_TYPE_PREMIE) {
      $field = 'bedrag_premie';
    }

    $this->connection->update('mf_fiscale_regeling')
      ->fields([
        $field => 0,
      ])
      ->condition('jaar', $jaar, '=')
      ->execute();

    $this->connection->delete('mf_fiscale_regeling')
      ->condition('jaar', $jaar, '=')
      ->condition('bedrag_begroting', 0, '=')
      ->condition('bedrag_fiscaal', 0, '=')
      ->condition('bedrag_premie', 0, '=')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Import file type'),
      '#required' => TRUE,
      '#options' => [
        self::IMPORT_TYPE_BEGROTING => $this->t('Begroting'),
        self::IMPORT_TYPE_FISCAAL => $this->t('Fiscaal'),
        self::IMPORT_TYPE_PREMIE => $this->t('Premie'),
      ],
      '#empty_option' => $this->t('- Select type -'),
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
    $type = $formState->getValue('type');

    // Delete all old data.
    $this->removeOldData($jaar, $type);

    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv);
      $lineNr = 1;
      while (($line = fgetcsv($csv)) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line);

        $values = [
          'jaar' => $jaar,
          'hoofdstuk_minfin_id' => NULL,
          'artikel_minfin_id' => NULL,
          'bedrag_begroting' => 0,
          'bedrag_premie' => 0,
          'bedrag_fiscaal' => 0,
        ];

        // Validate the begrotings data file and fill the values array.
        if ($type === self::IMPORT_TYPE_BEGROTING) {

          if ((int) $line[0] !== $jaar) {
            $args = ['%year' => $jaar, '@column' => 'A', '%value' => $line[0]];
            $message = "Year doesn't match. You're trying to import the data for %year, but the value in column @column is %value";
            $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
            $this->rowSkipped++;
            continue;
          }

          $values['hoofdstuk_minfin_id'] = $line[2];
          $values['artikel_minfin_id'] = $line[4];
          $values['bedrag_begroting'] = $this->fixCurrencyValues($line[6], $lineNr, 'G');
        }

        // Validate the fiscal data file and fill the values array.
        elseif ($type === self::IMPORT_TYPE_FISCAAL) {

          $artikel = $this->fixArtikel($this->extractValue($line[7]), $lineNr);
          if (!$artikel) {
            $message = $this->t("The given articlenumber wasn't valid.");
            $this->logError(self::SEVERITY_ERROR, $message, [], $lineNr);
            $this->rowSkipped++;
            continue;
          }

          $values['hoofdstuk_minfin_id'] = strtoupper($this->extractValue($line[6]));
          $values['artikel_minfin_id'] = $artikel;
          $values['bedrag_fiscaal'] = $this->fixCurrencyValues($line[5], $lineNr, 'F') * 1000000;
        }

        // Validate the premie data file and fill the values array.
        elseif ($type === self::IMPORT_TYPE_PREMIE) {

          if ((int) $line[0] !== $jaar) {
            $args = ['%year' => $jaar, '@column' => 'A', '%value' => $line[0]];
            $message = "Year doesn't match. You're trying to import the data for %year, but the value in column @column is %value";
            $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
            $this->rowSkipped++;
            continue;
          }

          $artikel = $this->fixArtikel($line[6], $lineNr);
          if (!$artikel) {
            $message = $this->t("The given articlenumber wasn't valid.");
            $this->logError(self::SEVERITY_ERROR, $message, [], $lineNr);
            $this->rowSkipped++;
            continue;
          }

          $values['hoofdstuk_minfin_id'] = $line[1];
          $values['artikel_minfin_id'] = $artikel;
          $values['bedrag_premie'] = $this->fixCurrencyValues($line[21], $lineNr, 'V') * 1000;
        }

        // Insert the actual values.
        try {
          $record = $this->connection->select('mf_fiscale_regeling', 'fr')
            ->fields('fr', [
              'bedrag_begroting',
              'bedrag_premie',
              'bedrag_fiscaal',
            ])
            ->condition('jaar', $jaar, '=')
            ->condition('hoofdstuk_minfin_id', $values['hoofdstuk_minfin_id'], '=')
            ->condition('artikel_minfin_id', $values['artikel_minfin_id'], '=')
            ->execute()
            ->fetchAssoc();

          $keys = [
            'jaar' => $jaar,
            'hoofdstuk_minfin_id' => $values['hoofdstuk_minfin_id'],
            'artikel_minfin_id' => $values['artikel_minfin_id'],
          ];
          $fields = $keys;

          if ($record) {
            $fields['bedrag_begroting'] = (empty($values['bedrag_begroting']) ? $values['bedrag_begroting'] + (int) $record['bedrag_begroting'] : $values['bedrag_begroting']);
            $fields['bedrag_premie'] = (empty($values['bedrag_premie']) ? $values['bedrag_premie'] + (int) $record['bedrag_premie'] : $values['bedrag_premie']);
            $fields['bedrag_fiscaal'] = (empty($values['bedrag_fiscaal']) ? $values['bedrag_fiscaal'] + (int) $record['bedrag_fiscaal'] : $values['bedrag_fiscaal']);
          }

          $this->connection->merge('mf_fiscale_regeling')
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

  /**
   * Helper function to extract the 'hoofdstuk' and 'artikel' id from the value.
   *
   * The 'hoofdstuk' id in this document is placed in the same column as the
   * name. Both are separated by a colon, so this function will extract the
   * value we need.
   *
   * @param string $value
   *   The intial value.
   * @param int $index
   *   The index of the explode $value to return.
   *
   * @return string
   *   Return the $index of the exploded $value.
   */
  private function extractValue($value, $index = 0): string {
    $array = explode(':', $value);

    return (isset($array[$index]) ? trim($array[$index]) : '');
  }

}
