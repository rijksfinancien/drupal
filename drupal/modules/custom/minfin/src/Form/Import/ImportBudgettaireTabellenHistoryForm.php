<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the budgettaire tabellen history importer.
 */
class ImportBudgettaireTabellenHistoryForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_budgettaire_tabellen_history_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'budgettaire_tabellen_history';
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
    return 2;
  }

  /**
   * Remove old data.
   */
  protected function removeOldData(): void {
    $this->connection->delete('mf_voorgaand_regeling_detailniveau')->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    unset($form['year']);

    $form['cleanup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Empty the complete database, before importing the new data.'),
      '#default_value' => FALSE,
      '#weight' => 49,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    // Delete all old data.
    if ($formState->getValue('cleanup')) {
      $this->removeOldData();
    }

    $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
    if ($csv = fopen($file->getFileUri(), 'rb')) {
      $queue = $this->queueFactory->get('budgettaire_tabellen_history_queue');

      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv, 0, $csvSeparator);
      $lineNr = 1;
      while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line);

        if ($line[0] === $line[1]) {
          $args = [
            '@column1' => 'A',
            '@column2' => 'B',
          ];
          $message = 'The value in column @column1 is exactly the same as the value in column @column2.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        $query = $this->connection->select('mf_b_tabel', 'bt');
        $query->addField('bt', 'regeling_detailniveau_id', 'id');
        $query->condition('bt.btabel_minfin_id', $line[0], '=');
        $voorgaandRegelingDetailniveauId = (int) $query->execute()->fetchField();
        if (!$voorgaandRegelingDetailniveauId) {
          $args = [
            '%table' => 'Regeling detailniveau',
            '%value' => $line[0],
            '@column' => 'A',
          ];
          $message = 'No %table database record found for the value %value in column @column.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        $query = $this->connection->select('mf_b_tabel', 'bt');
        $query->addField('bt', 'regeling_detailniveau_id', 'id');
        $query->condition('bt.btabel_minfin_id', $line[1], '=');
        $regelingDetailniveauId = (int) $query->execute()->fetchField();
        if (!$regelingDetailniveauId) {
          $args = [
            '%table' => 'Regeling detailniveau',
            '%value' => $line[1],
            '@column' => 'B',
          ];
          $message = 'No %table database record found for the value %value in column @column.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if ($voorgaandRegelingDetailniveauId != $regelingDetailniveauId) {
          $line0 = explode('.', $line[0]);
          $line1 = explode('.', $line[1]);
          $this->insertRecord($regelingDetailniveauId, (int) $line1[0], $line1[2], $voorgaandRegelingDetailniveauId, (int) $line0[0], $line0[2]);
          $queue->createItem([
            'id' => $regelingDetailniveauId,
            'jaar' => (int) $line1[0],
            'fase' => $line1[2],
          ]);
          $queue->createItem([
            'id' => $voorgaandRegelingDetailniveauId,
            'jaar' => (int) $line0[0],
            'fase' => $line0[2],
          ]);
          $this->rowsImported++;
        }
        else {
          $this->rowSkipped++;
        }
      }

      $this->messenger()->addMessage($this->t('Note the records have only been added to the queue. This queue will automatically be processed by the cron, but might take a while to complete.',));
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Insert the record into the database.
   *
   * @param int $regelingDetailniveauId
   *   The $regelingDetailniveauId.
   * @param int $jaar
   *   The year.
   * @param string $fase
   *   The phase.
   * @param int $voorgaandRegelingDetailniveauId
   *   The previous $regelingDetailniveauId.
   * @param int $voorgaandJaar
   *   The previous year.
   * @param string $voorgaandFase
   *   The previous phase.
   */
  private function insertRecord(int $regelingDetailniveauId, int $jaar, string $fase, int $voorgaandRegelingDetailniveauId, int $voorgaandJaar, string $voorgaandFase): void {
    try {
      $this->connection->merge('mf_voorgaand_regeling_detailniveau')
        ->keys([
          'voorgaand_regeling_detailniveau_id' => $regelingDetailniveauId,
          'regeling_detailniveau_id' => $voorgaandRegelingDetailniveauId,
        ])
        ->fields([
          'voorgaand_jaar' => $jaar,
          'voorgaand_fase' => $fase,
          'jaar' => $voorgaandJaar,
          'fase' => $voorgaandFase,
          'type' => 'reverse',
        ])
        ->execute();

      $this->connection->merge('mf_voorgaand_regeling_detailniveau')
        ->keys([
          'voorgaand_regeling_detailniveau_id' => $voorgaandRegelingDetailniveauId,
          'regeling_detailniveau_id' => $regelingDetailniveauId,
        ])
        ->fields([
          'voorgaand_jaar' => $voorgaandJaar,
          'voorgaand_fase' => $voorgaandFase,
          'jaar' => $jaar,
          'fase' => $fase,
          'type' => 'normal',
        ])
        ->execute();
    }
    catch (\Exception $e) {
      return;
    }
  }

}
