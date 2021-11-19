<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the budgettaire tabellen importer.
 */
class ImportBudgettaireTabellenForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_budgettaire_tabellen_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'budgettaire_tabellen';
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
    return 17;
  }

  /**
   * Remove old data.
   *
   * @param int $jaar
   *   Year.
   * @param string $fase
   *   Phase.
   */
  protected function removeOldData(int $jaar, string $fase): void {
    $fields = ['bedrag_begroting' => NULL];
    if ($fase === 'JV') {
      $fields = ['bedrag_jaarverslag' => NULL];
    }
    elseif ($fase === 'O1') {
      $fields = [
        'bedrag_vastgestelde_begroting' => NULL,
        'bedrag_suppletoire1' => NULL,
      ];
    }
    elseif ($fase === 'O2') {
      $fields = ['bedrag_suppletoire2' => NULL];
    }
    $this->connection->update('mf_b_tabel')
      ->fields($fields)
      ->condition('jaar', $jaar, '=')
      ->execute();

    // Now start removing the old data.
    $tables = [
      'mf_regeling_detailniveau' => 'regeling_detailniveau_id',
      'mf_instrument_of_uitsplitsing_apparaat' => 'instrument_of_uitsplitsing_apparaat_id',
      'mf_artikelonderdeel' => 'artikelonderdeel_id',
    ];
    foreach ($tables as $table => $field) {
      $query = $this->connection->select('mf_b_tabel', 'bt');
      $query->fields('bt', [$field]);
      $query->addExpression('SUM(bedrag_begroting)', 'begroting');
      $query->addExpression('SUM(bedrag_vastgestelde_begroting)', 'vastgestelde_begroting');
      $query->addExpression('SUM(bedrag_suppletoire1)', 'suppletoire1');
      $query->addExpression('SUM(bedrag_suppletoire2)', 'suppletoire2');
      $query->addExpression('SUM(bedrag_jaarverslag)', 'jaarverslag');
      $query->groupBy($field);
      $query->condition('jaar', $jaar, '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        if ($record['begroting'] === NULL && $record['vastgestelde_begroting'] === NULL && $record['suppletoire1'] === NULL && $record['suppletoire2'] === NULL && $record['jaarverslag'] === NULL) {
          $this->connection->delete($table)
            ->condition($field, $record[$field], '=')
            ->condition('jaar', $jaar, '=')
            ->execute();
        }
      }
    }

    $this->connection->delete('mf_b_tabel')
      ->condition('jaar', $jaar, '=')
      ->condition('bedrag_begroting', NULL, 'IS')
      ->condition('bedrag_vastgestelde_begroting', NULL, 'IS')
      ->condition('bedrag_suppletoire1', NULL, 'IS')
      ->condition('bedrag_suppletoire2', NULL, 'IS')
      ->condition('bedrag_jaarverslag', NULL, 'IS')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $form['phase'] = [
      '#type' => 'select',
      '#title' => $this->t('Phase'),
      '#required' => TRUE,
      '#options' => [
        'owb' => $this->t('OWB'),
        'o1' => $this->t('1e suppletoire'),
        'o2' => $this->t('2e suppletoire'),
        'jv' => $this->t('JV'),
      ],
      '#empty_option' => $this->t('- Select phase -'),
      '#weight' => 20,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    $phase = strtoupper($formState->getValue('phase'));
    $jaar = (int) $formState->getValue('year');

    // Delete all old data.
    $this->removeOldData($jaar, $phase);

    $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv, 0, $csvSeparator);
      $lineNr = 1;
      while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line);
        $this->lineNumbersToReadable($phase, $line);

        if ((int) $line['jaar'] !== $jaar) {
          $args = [
            '%year' => $jaar,
            '@column' => 'A',
            '%value' => $line['jaar'],
          ];
          $message = "Year doesn't match. You're trying to import the data for %year, but the value in column @column is %value";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if ($this->fixPhase((string) $line['fase']) !== $phase) {
          $args = [
            '%phase' => $phase,
            '@column' => 'F',
            '%value' => $line['fase'],
          ];
          $message = "Phase doesn't match. You're trying to import the data for %phase, but the value in column @column is %value";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if (!$line['vuo'] || !in_array(strtoupper($line['vuo']), ['V', 'U', 'O'])) {
          $args = [
            '@column' => 'M',
            '%value' => $line['vuo'],
            '%allowed_values' => $this->t('@a or @b', ['@a' => "'V', 'U'", '@b' => "'O'"]),
          ];
          $message = 'Column @column must contain either the value %allowed_values, but we found the value %value';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if (!$line['hoofdstuk_minfin_id'] || !$line['artikel_minfin_id']) {
          $args = ['@column' => 'B & G'];
          $message = "The following column(s) can't be empty: @column.";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        if (!$line['index']) {
          $column = 'AA';
          if ($phase === 'O1') {
            $column = 'AF';
          }
          elseif ($phase === 'OWB') {
            $column = 'AC';
          }
          $args = ['@column' => $column . ' (Index)'];
          $message = "The following column(s) can't be empty: @column.";
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        // Insert hoofdstuk.
        if (!$hoofdstukId = $this->insertHoofdstuk($line['hoofdstuk_minfin_id'], $line['hoofdstuk_naam'], $jaar)) {
          $args = [
            '@field' => 'hoofdstuk',
            '@column' => 'B & D',
          ];
          $message = 'Unexpected failure in column @column to insert the @field data.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        // Insert minister.
        if (!$line['minister']) {
          $args = [
            '@field' => 'minister',
            '@column' => 'C',
          ];
          $message = "Column @column was empty, so we couldn't import the @field data.";
          $this->logError(self::SEVERITY_WARNING, $message, $args, $lineNr);
        }
        else {
          $this->insertMinister($hoofdstukId, $line['minister'], $jaar, $phase);
        }

        // Insert artikel.
        if (!$artikelId = $this->insertArtikel($line['artikel_minfin_id'], $line['hoofdstuk_minfin_id'], $line['artikel_naam'], $jaar, $lineNr)) {
          $args = [
            '@field' => 'artikel',
            '@column' => 'G & I',
          ];
          $message = 'Unexpected failure in column @column to insert the @field data.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        // Insert artikel onderdeel.
        if (!$artikelonderdeelId = $this->insertArtikelOnderdeel($artikelId, $line['artikelonderdeel_minfin_id'], $line['artikelonderdeel_naam'], $jaar, $lineNr)) {
          $args = [
            '@field' => 'aritkelonderdeel',
            '@column' => 'P & Q',
          ];
          $message = 'Unexpected failure in column @column to insert the @field data.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        // Insert instrument of uitsplitsing apparaat.
        if (!$instrumentOfUitsplitsingApparaatId = $this->insertInstrumentOfUitsplitsingApparaat($artikelonderdeelId, $line['instrument_of_uitsplitsing_apparaat_minfin_id'], $line['instrument_of_uitsplitsing_apparaat_naam'], $jaar, $lineNr)) {
          $args = [
            '@field' => 'Instrument of uitsplitsing apparaat',
            '@column' => 'R & S',
          ];
          $message = 'Unexpected failure in column @column to insert the @field data.';
          $this->logError(self::SEVERITY_ERROR, $message, $args, $lineNr);
          $this->rowSkipped++;
          continue;
        }

        // Insert regeling detail niveau.
        $regelingDetailniveauId = $this->insertRegelingDetailniveau($instrumentOfUitsplitsingApparaatId, $line['regeling_detailniveau_naam'], $jaar);

        // Insert B tabel.
        $column = 'Z';
        if ($phase === 'O1') {
          $column = 'AA';
        }
        elseif ($phase === 'OWB') {
          $column = 'AC';
        }
        $bedrag = $this->fixCurrencyValues($line['bedrag'], $lineNr, $column);
        $this->insertBTabel($line['index'], $phase, $hoofdstukId, $artikelId, $artikelonderdeelId, $instrumentOfUitsplitsingApparaatId, $regelingDetailniveauId, $bedrag, $jaar, $line['vuo'], !empty($line['regeling_detailniveau_naam']));
        if ($phase === 'O1' && isset($line['bedrag2'])) {
          $bedrag2 = $this->fixCurrencyValues($line['bedrag2'], $lineNr, 'X');
          $this->insertBTabel($line['index'], 'VB', $hoofdstukId, $artikelId, $artikelonderdeelId, $instrumentOfUitsplitsingApparaatId, $regelingDetailniveauId, $bedrag2, $jaar, $line['vuo'], !empty($line['regeling_detailniveau_naam']));
        }
        $this->rowsImported++;
      }

      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function insertArtikel(string $minfinId, string $hoofdstukMinfinId, string $naam, int $jaar, int $lineNr = NULL): ?int {
    if (!is_numeric($minfinId)) {
      $message = $this->t("The given articlenumber wasn't valid.");
      $this->logError(self::SEVERITY_ERROR, $message, [], $lineNr);
      $this->rowSkipped++;
      return NULL;
    }

    $transaction = $this->connection->startTransaction();
    try {
      $this->connection->merge('mf_artikel')
        ->keys([
          'hoofdstuk_minfin_id' => $hoofdstukMinfinId,
          'artikel_minfin_id' => $minfinId,
          'jaar' => $jaar,
        ])
        ->fields([
          'naam' => $naam,
        ])
        ->execute();

      $id = $this->connection->select('mf_artikel')
        ->fields(NULL, ['artikel_id'])
        ->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
        ->condition('artikel_minfin_id', $minfinId, '=')
        ->condition('jaar', $jaar, '=')
        ->execute()
        ->fetchField();
      if ($id) {
        return $id;
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      if (strpos($e->getMessage(), 'Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction') !== FALSE) {
        sleep(1);
        return $this->insertArtikel($minfinId, $hoofdstukMinfinId, $naam, $jaar, $lineNr);
      }
      else {
        $this->logger('minfin import')->error($e->getMessage());
      }
    }
    return NULL;
  }

  /**
   * Insert a new 'artikelonderdeel' into the database.
   *
   * @param int $artikelId
   *   The artikel id.
   * @param mixed $minfinId
   *   The number indicating the 'artikelonderdeel'.
   * @param string $naam
   *   The name of the 'artikelonderdeel'.
   * @param int $jaar
   *   The year this 'artikelonderdeel' is active.
   * @param int|null $lineNr
   *   The line number.
   *
   * @return int|null
   *   The 'artikelonderdeel' id or null.
   */
  protected function insertArtikelOnderdeel(int $artikelId, $minfinId, string $naam, int $jaar, int $lineNr = NULL): ?int {
    if (!empty($minfinId) && !is_numeric($minfinId)) {
      $args = [
        '%value' => $minfinId,
        '@column' => 'Q',
        '%return' => (int) $minfinId,
      ];
      $message = "The value %value in column @column isn't a valid integer so the value has been changed to %return";
      $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);
    }

    $transaction = $this->connection->startTransaction();
    try {
      $newMinfinId = $minfinId;
      if ($newMinfinId === NULL) {
        $query = $this->connection->select('mf_artikelonderdeel', 'ao');
        $query->fields('ao', ['artikelonderdeel_id']);
        $query->condition('ao.jaar', $jaar, '=');
        $query->condition('ao.naam', $naam, '=');
        $newMinfinId = $query->execute()->fetchField();

        if (!$newMinfinId) {
          $query = $this->connection->select('mf_artikelonderdeel', 'ao');
          $query->addExpression('MAX(ao.artikelonderdeel_id)');
          $newMinfinId = $query->execute()->fetchField() + 1;
        }
      }
      $newMinfinId = (int) $newMinfinId;

      $this->connection->merge('mf_artikelonderdeel')
        ->keys([
          'artikelonderdeel_minfin_id' => $newMinfinId,
          'artikel_id' => $artikelId,
          'jaar' => $jaar,
        ])
        ->fields([
          'naam' => $naam,
        ])
        ->execute();

      $id = $this->connection->select('mf_artikelonderdeel')
        ->fields(NULL, ['artikelonderdeel_id'])
        ->condition('artikelonderdeel_minfin_id', $newMinfinId, '=')
        ->condition('artikel_id', $artikelId, '=')
        ->condition('jaar', $jaar, '=')
        ->execute()
        ->fetchField();
      if ($id) {
        return $id;
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      if (strpos($e->getMessage(), 'Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction') !== FALSE) {
        sleep(1);
        return $this->insertArtikelOnderdeel($artikelId, $minfinId, $naam, $jaar, $lineNr);
      }
      else {
        $this->logger('minfin import')->error($e->getMessage());
      }
    }
    return NULL;
  }

  /**
   * Insert a new 'instrument of uitsplitsing apparaat' into the database.
   *
   * @param int $artikelonderdeelId
   *   The artikelonderdeel id.
   * @param mixed $minfinId
   *   The number indicating the 'instrument of uitsplitsing apparaat'.
   * @param string $naam
   *   The name of the 'instrument of uitsplitsing apparaat'.
   * @param int $jaar
   *   The year this 'instrument of uitsplitsing apparaat' is active.
   * @param int|null $lineNr
   *   The line number.
   *
   * @return int|null
   *   The 'instrument of uitsplitsing apparaat' id or null.
   */
  protected function insertInstrumentOfUitsplitsingApparaat(int $artikelonderdeelId, $minfinId, string $naam, int $jaar, int $lineNr = NULL): ?int {
    if (!empty($minfinId) && !is_numeric($minfinId)) {
      $args = [
        '%value' => $minfinId,
        '@column' => 'S',
        '%return' => (int) $minfinId,
      ];
      $message = "The value %value in column @column isn't a valid integer so the value has been changed to %return";
      $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);
    }

    $transaction = $this->connection->startTransaction();
    try {
      $newMinfinId = $minfinId;
      if ($newMinfinId === NULL) {
        $query = $this->connection->select('mf_instrument_of_uitsplitsing_apparaat', 'iua');
        $query->fields('iua', ['instrument_of_uitsplitsing_apparaat_id']);
        $query->condition('iua.jaar', $jaar, '=');
        $query->condition('iua.naam', $naam, '=');
        $newMinfinId = $query->execute()->fetchField();

        if (!$newMinfinId) {
          $query = $this->connection->select('mf_instrument_of_uitsplitsing_apparaat', 'iua');
          $query->addExpression('MAX(iua.instrument_of_uitsplitsing_apparaat_id)');
          $newMinfinId = $query->execute()->fetchField() + 1;
        }
      }
      $newMinfinId = (int) $newMinfinId;

      $this->connection->merge('mf_instrument_of_uitsplitsing_apparaat')
        ->keys([
          'instrument_of_uitsplitsing_apparaat_minfin_id' => $newMinfinId,
          'artikelonderdeel_id' => $artikelonderdeelId,
          'jaar' => $jaar,
        ])
        ->fields([
          'naam' => $naam,
        ])
        ->execute();

      $id = $this->connection->select('mf_instrument_of_uitsplitsing_apparaat')
        ->fields(NULL, ['instrument_of_uitsplitsing_apparaat_id'])
        ->condition('instrument_of_uitsplitsing_apparaat_minfin_id', $newMinfinId, '=')
        ->condition('artikelonderdeel_id', $artikelonderdeelId, '=')
        ->condition('jaar', $jaar, '=')
        ->execute()
        ->fetchField();
      if ($id) {
        return $id;
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      if (strpos($e->getMessage(), 'Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction') !== FALSE) {
        sleep(1);
        return $this->insertInstrumentOfUitsplitsingApparaat($artikelonderdeelId, $minfinId, $naam, $jaar, $lineNr);
      }
      else {
        $this->logger('minfin import')->error($e->getMessage());
      }
    }
    return NULL;
  }

  /**
   * Insert a new 'regeling detail niveau' into the database.
   *
   * @param int $instrumentOfUitsplitsingApparaatId
   *   The instrument of uitsplitsing apparaat id.
   * @param string $naam
   *   The name of the 'artikelonderdeel'.
   * @param int $jaar
   *   The year this 'artikelonderdeel' is active.
   *
   * @return int|null
   *   The 'regeling detail' id or null.
   */
  protected function insertRegelingDetailniveau(int $instrumentOfUitsplitsingApparaatId, string $naam, int $jaar): ?int {
    $query = $this->connection->select('mf_regeling_detailniveau');
    $query->addExpression('MAX(regeling_detailniveau_id)');
    $minfinId = $query->execute()->fetchField() + 1;
    $naam = !empty($naam) ? $naam : '';

    $transaction = $this->connection->startTransaction();
    try {
      $this->connection->merge('mf_regeling_detailniveau')
        ->keys([
          'regeling_detailniveau_minfin_id' => $minfinId,
          'instrument_of_uitsplitsing_apparaat_id' => $instrumentOfUitsplitsingApparaatId,
          'jaar' => $jaar,
        ])
        ->fields([
          'naam' => $naam,
        ])
        ->execute();

      $id = $this->connection->select('mf_regeling_detailniveau')
        ->fields(NULL, ['regeling_detailniveau_id'])
        ->condition('regeling_detailniveau_minfin_id', $minfinId, '=')
        ->condition('instrument_of_uitsplitsing_apparaat_id', $instrumentOfUitsplitsingApparaatId, '=')
        ->condition('jaar', $jaar, '=')
        ->execute()
        ->fetchField();
      if ($id) {
        return $id;
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      if (strpos($e->getMessage(), 'Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction') !== FALSE) {
        sleep(1);
        return $this->insertRegelingDetailniveau($instrumentOfUitsplitsingApparaatId, $naam, $jaar);
      }
      else {
        $this->logger('minfin import')->error($e->getMessage());
      }
    }
    return NULL;
  }

  /**
   * Insert B-Tabel.
   *
   * @param string $minfinId
   *   MinfinId.
   * @param string $fase
   *   Fase.
   * @param int $hoofdstukId
   *   HoofdstukId.
   * @param string $artikelId
   *   ArtikelId.
   * @param string $artikelonderdeelId
   *   ArtikelonderdeelId.
   * @param string $instrumentOfUitsplitsingApparaatId
   *   InstrumentOfUitsplitsingApparaatId.
   * @param string|null $regelingDetailniveauId
   *   RegelingDetailniveauId.
   * @param int $bedrag
   *   Bedrag.
   * @param int $jaar
   *   Year.
   * @param string $vuo
   *   VUO.
   * @param bool $show
   *   Indicates if we need to show or hide the record in API's.
   */
  protected function insertBTabel(string $minfinId, string $fase, int $hoofdstukId, string $artikelId, string $artikelonderdeelId, string $instrumentOfUitsplitsingApparaatId, $regelingDetailniveauId, int $bedrag, int $jaar, string $vuo, bool $show = TRUE): void {
    $transaction = $this->connection->startTransaction();
    try {
      $fields = [
        'btabel_minfin_id' => $minfinId,
        'jaar' => $jaar,
        'vuo' => strtoupper($vuo),
        'hoofdstuk_id' => $hoofdstukId,
        'artikel_id' => $artikelId,
        'artikelonderdeel_id' => $artikelonderdeelId,
        'instrument_of_uitsplitsing_apparaat_id' => $instrumentOfUitsplitsingApparaatId,
        'show' => (int) $show,
      ];
      if ($regelingDetailniveauId) {
        $fields['regeling_detailniveau_id'] = $regelingDetailniveauId;
      }

      if (in_array($fase, ['OWB', 'JV', 'O1', 'O2', 'VB'])) {
        $bedragField = 'bedrag_begroting';
        if ($fase === 'JV') {
          $bedragField = 'bedrag_jaarverslag';
        }
        elseif ($fase === 'O1') {
          $bedragField = 'bedrag_suppletoire1';
        }
        elseif ($fase === 'O2') {
          $bedragField = 'bedrag_suppletoire2';
        }
        elseif ($fase === 'VB') {
          $bedragField = 'bedrag_vastgestelde_begroting';
        }

        // Increment the amount with any possible amount we already have in the
        // database for this row.
        $query = $this->connection->select('mf_b_tabel', 'b');
        $query->addField('b', $bedragField, 'bedrag');
        foreach ($fields as $k => $v) {
          $query->condition($k, $v, '=');
        }
        $bedragSum = (int) $query->execute()->fetchField() + $bedrag;

        // Now insert the values into the database through a merge.
        $query = $this->connection->merge('mf_b_tabel');
        $query->keys($fields);
        $query->fields($fields + [$bedragField => $bedragSum]);
        $query->execute();
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      if (strpos($e->getMessage(), 'Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction') !== FALSE) {
        sleep(1);
        $this->insertBTabel($minfinId, $fase, $hoofdstukId, $artikelId, $artikelonderdeelId, $instrumentOfUitsplitsingApparaatId, $regelingDetailniveauId, $bedrag, $jaar, $vuo);
      }
      else {
        $this->logger('minfin import')->error($e->getMessage());
      }
    }
  }

  /**
   * Insert a minister.
   *
   * @param int $hoofdstukId
   *   HoofdstukId.
   * @param string $naam
   *   Name.
   * @param int $jaar
   *   Year.
   * @param string $fase
   *   Phase.
   */
  protected function insertMinister(int $hoofdstukId, string $naam, int $jaar, string $fase): void {
    $transaction = $this->connection->startTransaction();
    try {
      $ministerId = $this->connection->select('mf_minister', 'm')
        ->fields('m', ['minister_id'])
        ->condition('naam', $naam, '=')
        ->execute()
        ->fetchField();
      if (!$ministerId) {
        $ministerId = $this->connection->insert('mf_minister')
          ->fields([
            'naam' => $naam,
          ])
          ->execute();
      }

      $row = $this->connection->select('mf_hoofdstuk_heeft_minister', 'hhm')
        ->fields('hhm', ['minister_id'])
        ->condition('jaar', $jaar, '=')
        ->condition('fase', $fase, '=')
        ->condition('hoofdstuk_id', $hoofdstukId, '=')
        ->execute()
        ->fetchAssoc();
      if (!$row) {
        $this->connection->insert('mf_hoofdstuk_heeft_minister')
          ->fields([
            'jaar' => $jaar,
            'fase' => $fase,
            'hoofdstuk_id' => $hoofdstukId,
            'minister_id' => $ministerId,
          ])
          ->execute();
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      if (strpos($e->getMessage(), 'Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction') !== FALSE) {
        sleep(1);
        $this->insertMinister($hoofdstukId, $naam, $jaar, $fase);
      }
      else {
        $this->logger('minfin import')->error($e->getMessage());
      }
    }
  }

  /**
   * Turn the numbered row from the csv file into an assoc array.
   *
   * @param string $fase
   *   Phase.
   * @param string[] $row
   *   The row to be cleaned.
   */
  private function lineNumbersToReadable(string $fase, array &$row): void {
    switch ($fase) {
      case 'JV':
      case 'O2':
        $row = [
          'jaar' => $row[0],
          'hoofdstuk_minfin_id' => $row[1],
          'minister' => $row[2],
          'hoofdstuk_naam' => $row[3],
          'fase' => $row[5],
          'artikel_minfin_id' => $row[6],
          'artikel_naam' => $row[8],
          'vuo' => $row[12],
          'artikelonderdeel_naam' => $row[15],
          'artikelonderdeel_minfin_id' => (isset($row[16]) && $row[16] !== '' ? $row[16] : NULL),
          'instrument_of_uitsplitsing_apparaat_naam' => $row[17],
          'instrument_of_uitsplitsing_apparaat_minfin_id' => (isset($row[18]) && $row[18] !== '' ? $row[18] : NULL),
          'regeling_detailniveau_naam' => $row[19],
          'bedrag' => $row[25],
          'index' => $row[26],
        ];
        break;

      case 'O1':
        $row = [
          'jaar' => $row[0],
          'hoofdstuk_minfin_id' => $row[1],
          'minister' => $row[2],
          'hoofdstuk_naam' => $row[3],
          'fase' => $row[5],
          'artikel_minfin_id' => $row[6],
          'artikel_naam' => $row[8],
          'vuo' => $row[12],
          'artikelonderdeel_naam' => $row[15],
          'artikelonderdeel_minfin_id' => (isset($row[16]) && $row[16] !== '' ? $row[16] : NULL),
          'instrument_of_uitsplitsing_apparaat_naam' => $row[17],
          'instrument_of_uitsplitsing_apparaat_minfin_id' => (isset($row[18]) && $row[18] !== '' ? $row[18] : NULL),
          'regeling_detailniveau_naam' => $row[19],
          'bedrag' => $row[26],
          'bedrag2' => $row[23],
          'index' => $row[31],
        ];
        break;

      case 'OWB':
      default:
        $row = [
          'jaar' => $row[0],
          'hoofdstuk_minfin_id' => $row[1],
          'minister' => $row[2],
          'hoofdstuk_naam' => $row[3],
          'fase' => $row[5],
          'artikel_minfin_id' => $row[6],
          'artikel_naam' => $row[8],
          'vuo' => $row[12],
          'artikelonderdeel_naam' => $row[15],
          'artikelonderdeel_minfin_id' => (isset($row[16]) && $row[16] !== '' ? $row[16] : NULL),
          'instrument_of_uitsplitsing_apparaat_naam' => $row[17],
          'instrument_of_uitsplitsing_apparaat_minfin_id' => (isset($row[18]) && $row[18] !== '' ? $row[18] : NULL),
          'regeling_detailniveau_naam' => $row[19],
          'bedrag' => $row[23],
          'index' => $row[28],
        ];
        break;
    }
  }

}
