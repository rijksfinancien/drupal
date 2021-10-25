<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the beleidsevaluaties importer.
 */
class ImportBeleidsevaluatiesForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_beleidsevaluaties_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'beleidsevaluaties';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportFileTypes(): array {
    return ['csv'];
  }

  /**
   * Remove old data.
   */
  protected function removeOldData(): void {
    $this->connection->delete('mf_beleidsevaluatie')->execute();
    $this->connection->delete('mf_beleidsevaluatie_artikel')->execute();
    $this->connection->delete('mf_beleidsevaluatie_hoofdstuk')->execute();
    $this->connection->delete('mf_beleidsevaluatie_thema')->execute();
    $this->connection->delete('mf_beleidsevaluatie_bijlage')->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    unset($form['year']);
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
    // Delete all old data.
    $this->removeOldData();

    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv);
      $lineNr = 1;
      while (($line = fgetcsv($csv)) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line);

        try {
          $fields = [
            'titel' => $line[0],
            'departement' => $line[1],
            'artikel' => $line[2],
            'type' => $line[4],
            'status' => $line[5],
            'opleverdatum' => $line[6],
            'toelichting' => $line[13],
            'onafhankelijke_deskundige' => $line[14],
            'hoofdrapport' => $line[15],
            'aankondiging' => $line[16],
            'kabinetsreactie_aanbiedingsbrief' => $line[17],
            'open_data' => $line[18],
          ];
          if (!empty($line[11])) {
            $fields['sea'] = $line[11];
          }

          $beleidsevaluatieId = $this->connection->insert('mf_beleidsevaluatie')
            ->fields($fields)
            ->execute();

          if (!$beleidsevaluatieId) {
            $this->logError(self::SEVERITY_ERROR, 'An unexpected error occurred while trying to import the this row.', [], $lineNr);
            $this->rowSkipped++;
            continue;
          }

          $jaar = (int) $line[6];
          if ($this->minfinService->getLastYear() < $jaar) {
            $jaar = $this->minfinService->getLastYear();
          }
          elseif ($this->minfinService->getFirstYear() > $jaar) {
            $jaar = $this->minfinService->getFirstYear();
          }

          $hoofdstukMinfinIds = [];
          if (!empty($line[7])) {
            $hoofdstukMinfinIds = explode(',', $line[7]);
            foreach ($hoofdstukMinfinIds as $hoofdstukMinfinId) {
              $hoofdstukId = $this->connection->select('mf_hoofdstuk', 'h')
                ->fields('h', ['hoofdstuk_id'])
                ->condition('h.jaar', $jaar, '=')
                ->condition('h.hoofdstuk_minfin_id', trim($hoofdstukMinfinId), '=')
                ->execute()->fetchField();

              if ($hoofdstukId) {
                $this->connection->merge('mf_beleidsevaluatie_hoofdstuk')
                  ->keys([
                    'beleidsevaluatie_id' => $beleidsevaluatieId,
                    'hoofdstuk_id' => $hoofdstukId,
                  ])
                  ->execute();
              }
              else {
                $args = ['@type' => 'hoofdstuk', '%value' => $hoofdstukMinfinId];
                $message = 'No matching @type found for %value.';
                $this->logError(self::SEVERITY_WARNING, $message, $args, $lineNr);
              }
            }
          }

          // @todo also add a block to import the themes.
          if (!empty($line[8])) {
            if (count($hoofdstukMinfinIds) !== 1) {
              $this->logError(self::SEVERITY_WARNING, 'The number of chapters is too large to find a database match for articles.', [], $lineNr);
            }
            else {
              foreach (explode(',', $line[8]) as $artikelMinfinId) {
                $artikelId = $this->connection->select('mf_artikel', 'a')
                  ->fields('a', ['artikel_id'])
                  ->condition('a.jaar', $jaar, '=')
                  ->condition('a.hoofdstuk_minfin_id', trim($hoofdstukMinfinId), '=')
                  ->condition('a.artikel_minfin_id', (int) trim($artikelMinfinId), '=')
                  ->execute()->fetchField();

                if ($artikelId) {
                  $this->connection->merge('mf_beleidsevaluatie_artikel')
                    ->keys([
                      'beleidsevaluatie_id' => $beleidsevaluatieId,
                      'artikel_id' => $artikelId,
                    ])
                    ->execute();
                }
                else {
                  $args = ['@type' => 'artikel', '%value' => $artikelMinfinId];
                  $message = 'No matching @type found for %value.';
                  $this->logError(self::SEVERITY_WARNING, $message, $args, $lineNr);
                }
              }
            }
          }

          // Import all possible attachments.
          for ($i = 19; $i <= 28; $i++) {
            if (!empty($line[$i])) {
              $this->connection->insert('mf_beleidsevaluatie_bijlage')
                ->fields([
                  'beleidsevaluatie_id' => $beleidsevaluatieId,
                  'bijlage' => $line[$i],
                ])
                ->execute();
            }
          }
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
