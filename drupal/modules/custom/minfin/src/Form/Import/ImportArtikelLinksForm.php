<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileInterface;

/**
 * Provides the artikel links importer.
 */
class ImportArtikelLinksForm extends ImportBaseForm {

  /**
   * Defines the beleidsdoorlichting import type.
   */
  protected const IMPORT_TYPE_CBS = 'cbs';

  /**
   * Defines the beleidsinformatie import type.
   */
  protected const IMPORT_TYPE_BELEIDSINFORMATIE = 'performance information';

  /**
   * Defines the beleidsdoorlichting import type.
   */
  protected const IMPORT_TYPE_BELEIDSDOORLICHTING = 'policy review';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_import_artikel_links_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportType(): string {
    return 'artikel_links';
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
    return 10;
  }

  /**
   * Remove old data.
   *
   * @param string $type
   *   The type.
   */
  protected function removeOldData(string $type): void {
    $this->connection->delete('mf_artikel_link')
      ->condition('category', $type, '=')
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    unset($form['year']);

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#required' => TRUE,
      '#options' => [
        self::IMPORT_TYPE_CBS => $this->t('CBS links'),
        self::IMPORT_TYPE_BELEIDSINFORMATIE => $this->t('Performance information'),
        self::IMPORT_TYPE_BELEIDSDOORLICHTING => $this->t('Policy review'),
      ],
      '#empty_option' => $this->t('- Select type -'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function submit(FileInterface $file, FormStateInterface $formState): bool {
    $type = (string) $formState->getValue('type');

    // Delete all old data.
    $this->removeOldData($type);

    $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
    if ($csv = fopen($file->getFileUri(), 'rb')) {
      // Skip the first line and then start looping over the following lines.
      fgetcsv($csv, 0, $csvSeparator);
      $lineNr = 1;
      while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
        $lineNr++;
        $links = [];

        // Import the beleidsinformatie and cbs links.
        // They share the same layout for the fields that mather.
        if ($type === self::IMPORT_TYPE_BELEIDSINFORMATIE || $type === self::IMPORT_TYPE_CBS) {
          $hoofdstukMinfinId = $this->fixHoofdstuk($line[1], $lineNr);
          $artikelMinfinId = $this->fixArtikel($line[3], $lineNr);

          $linkIndexes = [
            6 => 7,
            8 => 9,
            10 => 11,
            12 => 13,
            14 => 15,
            16 => 17,
            18 => 19,
          ];

          foreach ($linkIndexes as $indexLink => $indexDescr) {
            $link = isset($line[$indexLink]) && !empty($line[$indexLink]) ? trim($line[$indexLink]) : NULL;
            $title = isset($line[$indexDescr]) && !empty($line[$indexDescr]) ? trim($line[$indexDescr]) : NULL;

            if (isset($link)) {
              if (filter_var($link, FILTER_VALIDATE_URL)) {
                $links[] = [
                  'link' => $link,
                  'description' => $title,
                ];
              }
              else {
                $args = ['%url' => $link];
                $message = "The given value %url isn't a valid URL.";
                $this->logError(self::SEVERITY_WARNING, $message, $args, $lineNr);
              }
            }
          }
        }
        // Import the beleidsdoorlichting.
        elseif ($type === self::IMPORT_TYPE_BELEIDSDOORLICHTING) {
          if (strtolower($line[7]) === 'beleidsdoorlichting' && strtolower($line[12]) === 'afgerond') {
            $hoofdstukMinfinId = $line[1];
            $artikelMinfinId = $this->fixArtikel($line[3], $lineNr);
            if (isset($line[15]) && filter_var($line[15], FILTER_VALIDATE_URL)) {
              $links[] = [
                'link' => $line[15],
                'description' => $line[14],
              ];
            }
            else {
              $args = ['%url' => $line[15]];
              $message = "The given value %url isn't a valid URL.";
              $this->logError(self::SEVERITY_WARNING, $message, $args, $lineNr);
            }
          }
        }

        try {
          $fields = [
            'hoofdstuk_minfin_id' => $hoofdstukMinfinId,
            'artikel_minfin_id' => $artikelMinfinId,
            'category' => $formState->getValue('type'),
          ];
          foreach ($links as $link) {
            $fields['link'] = $link['link'];
            $fields['description'] = $link['description'];

            $this->connection->insert('mf_artikel_link')
              ->fields($fields)
              ->execute();
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
