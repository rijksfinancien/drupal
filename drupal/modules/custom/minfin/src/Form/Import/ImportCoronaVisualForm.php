<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the corona visual importer.
 */
class ImportCoronaVisualForm extends ImportBaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_corona_visual_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'corona_visual';
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
    switch ($formState->getValue('sub_type')) {
      case 'fiscalemaatregelen':
      case 'garanties':
      case 'leningen':
      case 'uitgavenmaatregelen':
        return 9;

      case 'plafond':
        return 8;

      case 'tijdlijn_noodpakketten':
        return 6;

      case 'toelichting_fiscalemaatregelen':
      case 'toelichting_garanties':
      case 'toelichting_leningen':
      case 'toelichting_uitgavenmaatregelen':
        return 5;

      case 'emu_saldo':
      case 'emu_schuld':
      case 'plafond_hoofdstukken':
      case 'toelichting_belastinguitstel':
        return 4;

      case 'automatische_stablisatoren_inkomsten':
      case 'automatische_stablisatoren_uitgaven':
      case 'belastinguitstel':
        return 3;

      case 'endogene_ontwikkelingen':
        return 2;
    }

    return NULL;
  }

  /**
   * Remove old data.
   *
   * @param string $type
   *   The type.
   */
  protected function removeOldData(string $type): void {
    switch ($type) {
      case 'emu_saldo':
      case 'emu_schuld':
        $this->connection->delete('mf_corona_emu')
          ->condition('type', $type, '=')
          ->execute();
        break;

      case 'endogene_ontwikkelingen':
      case 'belastinguitstel':
      case 'plafond_hoofdstukken':
      case 'fiscalemaatregelen':
      case 'garanties':
      case 'leningen':
      case 'tijdlijn_noodpakketten':
      case 'uitgavenmaatregelen':
        $this->connection->delete('mf_corona_visuals_data')
          ->condition('type', $type, '=')
          ->execute();
        break;

      case 'automatische_stablisatoren_inkomsten':
      case 'automatische_stablisatoren_uitgaven':
      case 'plafond':
        $this->connection->delete('mf_corona_visuals')
          ->condition('type', $type, '=')
          ->execute();
        break;

      case 'toelichting_belastinguitstel':
      case 'toelichting_fiscalemaatregelen':
      case 'toelichting_garanties':
      case 'toelichting_leningen':
      case 'toelichting_uitgavenmaatregelen':
        $this->connection->delete('mf_corona_visuals_uitleg')
          ->condition('type', substr($type, 12), '=')
          ->execute();
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    unset($form['year']);

    $types = [
      'automatische_stablisatoren_inkomsten' => 'Automatische Stablisatoren Inkomsten',
      'automatische_stablisatoren_uitgaven' => 'Automatische Stablisatoren Uitgaven',
      'belastinguitstel' => 'Belastinguitstel',
      'toelichting_belastinguitstel' => 'Belastinguitstel (toelichting)',
      'emu_saldo' => 'Emu saldo',
      'emu_schuld' => 'Emu schuld',
      'endogene_ontwikkelingen' => 'Endogene ontwikkelingen',
      'fiscalemaatregelen' => 'Fiscalemaatregelen',
      'toelichting_fiscalemaatregelen' => 'Fiscalemaatregelen (toelichting)',
      'garanties' => 'Garanties',
      'toelichting_garanties' => 'Garanties (toelichting)',
      'leningen' => 'Leningen',
      'toelichting_leningen' => 'Leningen (toelichting)',
      'plafond' => 'Plafond',
      'plafond_hoofdstukken' => 'Plafond: Corona hoofdstukken',
      'tijdlijn_noodpakketten' => 'Tijdlijn noodpakketten',
      'uitgavenmaatregelen' => 'Uitgavenmaatregelen',
      'toelichting_uitgavenmaatregelen' => 'Uitgavenmaatregelen (toelichting)',
    ];

    $form['last_update'] = [
      '#type' => 'date',
      '#title' => $this->t('Update date'),
      '#required' => TRUE,
      '#date_date_format' => 'd-m-Y',
      '#default_value' => date('Y-m-d'),
    ];

    $form['sub_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#required' => TRUE,
      '#options' => $types,
      '#empty_option' => $this->t('- Select type -'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    $type = $formState->getValue('sub_type');

    // Delete all old data.
    $this->removeOldData($type);

    $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
    $csvEncoding = $this->determineFileEncoding($file->getFileUri());
    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      $header = fgetcsv($csv, 0, $csvSeparator);
      $lineNr = 1;
      while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
        $lineNr++;
        $this->cleanupImportRow($line, $csvEncoding);

        $values = [];
        switch ($type) {
          case 'emu_saldo':
          case 'emu_schuld':
            if ($lineNr >= 2 && $lineNr <= 4) {
              $base = [
                'type' => $type,
              ];

              foreach (range(1, 10) as $i) {
                if (!$line[$i]) {
                  continue;
                }

                if ($header[$i] && (substr($header[$i], 0, 6) === 'Bedrag') && is_numeric(substr($header[$i], 7, 4))) {
                  $value = str_replace('%', '', $line[$i]);
                  $value = trim(str_replace(',', '.', $value));
                  if (is_numeric($value)) {
                    $base['jaar'] = substr($header[$i], 7, 4);
                    $base['value' . ($lineNr - 1)] = $value;
                    $values[] = $base;
                  }
                }
                else {
                  $this->logError(self::SEVERITY_ERROR, "Couldn't get a valid year from the header", [], $lineNr);
                  $this->rowSkipped++;
                }
              }

              if ($values) {
                foreach ($values as $row) {
                  $this->connection->merge('mf_corona_emu')
                    ->keys([
                      'type' => $row['type'],
                      'jaar' => $row['jaar'],
                    ])
                    ->fields($row)
                    ->execute();
                }
                $this->rowsImported++;
              }
              else {
                $this->logError(self::SEVERITY_ERROR, 'No importable values found', [], $lineNr);
                $this->rowSkipped++;
              }
            }
            break;

          case 'fiscalemaatregelen':
          case 'garanties':
          case 'leningen':
          case 'plafond_hoofdstukken':
          case 'uitgavenmaatregelen':
            $base = [
              'type' => $type,
              'niveau1_id' => ($type === 'uitgavenmaatregelen' ? $line[0] : $line[1]),
              'niveau1' => $line[1],
              'niveau2' => $line[2],
            ];

            foreach (array_combine(range(3, 13), range(2020, 2030)) as $i => $year) {
              if ($line[$i] !== NULL) {
                $base['datum'] = $year;
                $base['bedrag'] = $this->fixBedrag($csvEncoding, $line[$i], $lineNr);
                $values[] = $base;
              }
            }

            if ($values) {
              foreach ($values as $row) {
                $this->connection->insert('mf_corona_visuals_data')
                  ->fields($row)
                  ->execute();
              }
              $this->rowsImported++;
            }
            else {
              $this->logError(self::SEVERITY_ERROR, 'No importable values found', [], $lineNr);
              $this->rowSkipped++;
            }
            break;

          case 'belastinguitstel':
            $this->connection->insert('mf_corona_visuals_data')
              ->fields([
                'type' => $type,
                'niveau1_id' => $line[0],
                'niveau1' => $line[0],
                'niveau2' => $line[1],
                'datum' => '2020',
                'bedrag' => $this->fixBedrag($csvEncoding, $line[2], $lineNr),
              ])
              ->execute();
            $this->rowsImported++;
            break;

          case 'endogene_ontwikkelingen':
            $this->connection->insert('mf_corona_visuals_data')
              ->fields([
                'type' => $type,
                'niveau1_id' => $line[0],
                'niveau1' => $line[0],
                'niveau2' => NULL,
                'datum' => '2020',
                'bedrag' => $this->fixBedrag($csvEncoding, $line[1], $lineNr),
              ])
              ->execute();
            $this->rowsImported++;
            break;

          case 'plafond':
            $base = [
              'type' => $type,
              'position' => $line[0],
              'label' => $line[1],
            ];

            foreach (array_combine(range(2, 12), range(2020, 2030)) as $i => $year) {
              if ($line[$i] !== NULL) {
                $base['jaar'] = $year;
                $base['bedrag'] = $this->fixBedrag($csvEncoding, $line[$i], $lineNr);
                $values[] = $base;
              }
            }

            if ($values) {
              foreach ($values as $row) {
                $this->connection->insert('mf_corona_visuals')
                  ->fields($row)
                  ->execute();
              }
              $this->rowsImported++;
            }
            else {
              $this->logError(self::SEVERITY_ERROR, 'No importable values found', [], $lineNr);
              $this->rowSkipped++;
            }
            break;

          case 'automatische_stablisatoren_inkomsten':
          case 'automatische_stablisatoren_uitgaven':
            $this->connection->insert('mf_corona_visuals')
              ->fields([
                'type' => $type,
                'position' => $line[0],
                'label' => $line[1],
                'jaar' => '2020',
                'bedrag' => $this->fixBedrag($csvEncoding, $line[2], $lineNr),
              ])
              ->execute();
            $this->rowsImported++;
            break;

          case 'tijdlijn_noodpakketten':
            $this->connection->insert('mf_corona_visuals_data')
              ->fields([
                'type' => $type,
                'niveau1_id' => $line[2],
                'niveau1' => $line[3],
                'niveau2' => $line[4],
                'datum' => $line[1],
                'bedrag' => $this->fixBedrag($csvEncoding, $line[5], $lineNr),
              ])
              ->execute();
            $this->rowsImported++;
            break;

          case 'toelichting_belastinguitstel':
            $this->connection->insert('mf_corona_visuals_uitleg')
              ->fields([
                'type' => substr($type, 12),
                'niveau1_id' => 0,
                'niveau2' => 0,
                'toelichting' => $line[1],
                'uitleg' => $line[2],
                'link' => $line[3],
              ])
              ->execute();
            $this->rowsImported++;
            break;

          case 'toelichting_fiscalemaatregelen':
            $this->connection->insert('mf_corona_visuals_uitleg')
              ->fields([
                'type' => substr($type, 12),
                'niveau1_id' => $line[1],
                'niveau2' => $line[2],
                'toelichting' => $line[3],
                'uitleg' => $line[4],
              ])
              ->execute();
            $this->rowsImported++;
            break;

          case 'toelichting_garanties':
            $this->connection->insert('mf_corona_visuals_uitleg')
              ->fields([
                'type' => substr($type, 12),
                'niveau1_id' => $line[0],
                'niveau2' => $line[1],
                'toelichting' => $line[2],
                'uitleg' => $line[3],
                'link' => $line[5] ?? '',
              ])
              ->execute();
            $this->rowsImported++;
            break;

          case 'toelichting_leningen':
          case 'toelichting_uitgavenmaatregelen':
            $this->connection->insert('mf_corona_visuals_uitleg')
              ->fields([
                'type' => substr($type, 12),
                'niveau1_id' => $line[0],
                'niveau2' => $line[2],
                'toelichting' => $line[3],
                'uitleg' => $line[4],
                'link' => $line[5] ?? '',
              ])
              ->execute();
            $this->rowsImported++;
            break;
        }
      }

      $config = $this->configFactory()->getEditable('minfin_corona_visuals.last_update');
      $config->set($type, date('d-m-Y', strtotime($formState->getValue('last_update'))));
      $config->save();

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Clean up the bedrag values.
   *
   * @param string $encoding
   *   The character encoding.
   * @param string|float|int $value
   *   The value that needs to be cleaned up.
   * @param int|null $lineNr
   *   If known the line on which the value was found.
   *
   * @return float
   *   The cleaned up version of $value.
   */
  protected function fixBedrag(string $encoding, $value, int $lineNr = NULL): float {
    if (is_numeric($value)) {
      return (float) $value;
    }

    if ($encoding == 'ISO-8859-1' && substr_count($value, ',') === 1 && substr_count($value, '.') === 0) {
      $returnValue = str_replace(',', '.', $value);
      if (is_numeric($returnValue)) {
        $args = ['%value' => $value, '%return' => $returnValue];
        $message = "We assume the comma was meant as a decimal separator so we've changed the value %value to %return";
        $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);

        return (float) $returnValue;
      }
    }

    return 0;
  }

}
