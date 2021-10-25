<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * The importer for 'kamerstuk pdf'.
 */
class ImportKamerstukPdfForm extends ImportKamerstukBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_kamerstukpdf_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'kamerstuk_pdf';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportFileTypes(): array {
    return ['pdf', 'csv'];
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
   * @param string $phase
   *   The phase.
   * @param bool $appendix
   *   If this kamerstuk is an appendix or not.
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   */
  protected function removeOldData(string $phase, bool $appendix, string $type, int $year, ?string $hoofdstukMinfinId): void {
    $query = $this->connection->select('mf_kamerstuk_files', 'kf');
    $query->fields('kf', ['fid', 'kamerstuk_file_id']);
    $query->condition('type', $type, '=');
    $query->condition('fase', $phase, '=');
    $query->condition('bijlage', (int) $appendix, '=');
    $query->condition('jaar', $year, '=');
    if ($hoofdstukMinfinId) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    }
    else {
      $query->isNull('hoofdstuk_minfin_id');
    }
    if ($result = $query->execute()->fetchAssoc()) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->fileStorage->load($result['fid']);
      $this->fileUsage->delete($file, 'minfin', 'kamerstuk_file', $result['kamerstuk_file_id']);
      $file->delete();
    }

    $query = $this->connection->delete('mf_kamerstuk_files');
    $query->condition('type', $type, '=');
    $query->condition('fase', $phase, '=');
    $query->condition('bijlage', (int) $appendix, '=');
    $query->condition('jaar', $year, '=');
    if ($hoofdstukMinfinId) {
      $query->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
    }
    else {
      $query->isNull('hoofdstuk_minfin_id');
    }
    $query->execute();
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
        'pdf' => $this->t('PDF'),
        'csv' => $this->t('CSV'),
      ],
      '#default_value' => 'pdf',
      '#weight' => 1,
    ];

    $form['hoofdstuk_minfin_id']['#states']['required'] = $form['hoofdstuk_minfin_id']['#states']['visible'];
    foreach (['year', 'phase', 'phase_suffix', 'hoofdstuk_minfin_id'] as $field) {
      $form[$field]['#required'] = FALSE;
      $form[$field]['#states']['visible']['select[name="import_type"]'][] = ['value' => 'pdf'];
      $form[$field]['#states']['required']['select[name="import_type"]'][] = ['value' => 'pdf'];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    $importType = $formState->getValue('import_type');

    // CSV import.
    if ($importType === 'csv') {
      $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
      if ($csv = fopen($file->getFileUri(), 'rb')) {
        // Skip the first line and then start looping over the following lines.
        fgetcsv($csv, 0, $csvSeparator);
        $lineNr = 1;
        while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
          $lineNr++;
          if (isset($line[1], $line[2], $line[4])) {
            $phase = $this->getRealPhase($line[2]);
            $appendix = $this->isKamerstukAppendix($line[2]);
            $type = $this->getRealType($line[2]);
            $year = (int) $line[1];
            $hoofdstukMinfinId = $line[3] ?? NULL;

            $this->removeOldData($phase, $appendix, $type, $year, $hoofdstukMinfinId);
            $explode = explode('/', $line[4]);
            if ($pdfFile = system_retrieve_file($line[4], 'public://kamerstuk_pdf/' . end($explode), TRUE)) {
              $this->savePdfFile($pdfFile, $phase, $appendix, $type, $year, $hoofdstukMinfinId, $lineNr);
            }
            else {
              $this->logError(self::SEVERITY_ERROR, 'Failed to load the PDF file object.', [], $lineNr);
              $this->rowSkipped++;
            }
          }
        }
      }
    }

    // PDF import.
    elseif ($importType === 'pdf') {
      $importPhase = $formState->getValue('phase');
      if (in_array($importPhase, ['isb (mvt)', 'isb (wet)'])) {
        $importPhase = $formState->getValue('phase_suffix') . 'e ' . $importPhase;
      }
      $phase = $this->getRealPhase($importPhase);
      $appendix = $this->isKamerstukAppendix($importPhase);
      $type = $this->getRealType($importPhase);
      $year = (int) $formState->getValue('year');
      $hoofdstukMinfinId = $formState->getValue('hoofdstuk_minfin_id');

      $this->removeOldData($phase, $appendix, $type, $year, $hoofdstukMinfinId);
      $file = file_move($file, 'public://kamerstuk_pdf/' . $file->getFilename());
      $this->savePdfFile($file, $phase, $appendix, $type, $year, $hoofdstukMinfinId);
    }

    return TRUE;
  }

  /**
   * Save the actual pdf file.
   *
   * @param \Drupal\file\FileInterface $file
   *   A file entity.
   * @param string $phase
   *   The phase.
   * @param bool $appendix
   *   If this kamerstuk is an appendix or not.
   * @param string $type
   *   The type.
   * @param int $year
   *   The year.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param int|null $lineNr
   *   The line number.
   */
  private function savePdfFile(FileInterface $file, string $phase, bool $appendix, string $type, int $year, ?string $hoofdstukMinfinId, ?int $lineNr = NULL): void {
    // Insert in database.
    $fields = [
      'type' => $type,
      'fase' => $phase,
      'bijlage' => (int) $appendix,
      'jaar' => $year,
      'fid' => $file->id(),
    ];
    if ($hoofdstukMinfinId) {
      $fields['hoofdstuk_minfin_id'] = $hoofdstukMinfinId;
    }
    $kamerstukFileId = $this->connection->insert('mf_kamerstuk_files')
      ->fields($fields)
      ->execute();

    if (!$kamerstukFileId) {
      $this->logError(self::SEVERITY_ERROR, 'Failed to save the PDF file on the server.', [], $lineNr);
      $this->rowSkipped++;
      return;
    }

    // Permanently save the file.
    $this->fileUsage->add($file, 'minfin', 'kamerstuk_file', $kamerstukFileId);
    $file->setPermanent();
    $file->save();
    $this->rowsImported++;
  }

}
