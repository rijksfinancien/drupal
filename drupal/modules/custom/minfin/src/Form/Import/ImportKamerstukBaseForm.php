<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Core\Form\FormStateInterface;
use Drupal\minfin\KamerstukFormTrait;

/**
 * Provides the base definition for the kamerstuk import forms.
 */
abstract class ImportKamerstukBaseForm extends ImportBaseForm {
  use KamerstukFormTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);

    $phases = [];
    foreach (array_keys($this->getAvailableTypes()) as $phase) {
      $phases[$phase] = $phase;
    }
    ksort($phases);
    $form['phase'] = [
      '#type' => 'select',
      '#title' => $this->t('Phase'),
      '#required' => TRUE,
      '#options' => $phases,
      '#empty_option' => $this->t('- Select phase -'),
      '#weight' => 20,
    ];

    $form['phase_suffix'] = [
      '#type' => 'number',
      '#title' => $this->t('Phase suffix'),
      '#weight' => 21,
      '#states' => [
        'visible' => [
          'select[name="phase"]' => [
            ['value' => 'isb (mvt)'],
            ['value' => 'isb (wet)'],
          ],
        ],
        'required' => [
          'select[name="phase"]' => [
            ['value' => 'isb (mvt)'],
            ['value' => 'isb (wet)'],
          ],
        ],
      ],
    ];

    $form['hoofdstuk_minfin_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chapter'),
      '#size' => 5,
      '#weight' => 25,
      '#states' => [
        'visible' => [
          'select[name="phase"]' => [
            ['value' => 'jv'],
            ['value' => 'owb'],
            ['value' => 'owb (wet)'],
            ['value' => '1supp'],
            ['value' => '1supp (wet)'],
            ['value' => '2supp'],
            ['value' => '2supp (wet)'],
            ['value' => 'isb (mvt)'],
            ['value' => 'isb (wet)'],
            ['value' => 'sw (mvt)'],
            ['value' => 'sw'],
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $formState): void {
    $importType = $formState->getValue('import_type');

    if ($importType === 'csv' && $formState->getTriggeringElement()['#name'] !== 'file_remove_button') {
      // Load uploaded file.
      $file = NULL;
      $fileId = $formState->getValue('file', []);
      if ($fileId = reset($fileId)) {
        /** @var \Drupal\file\FileInterface $file */
        $file = $this->fileStorage->load($fileId);
      }

      if ($file) {
        $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
        if ($csv = fopen($file->getFileUri(), 'rb')) {
          // Skip the first line and start looping over the following lines.
          fgetcsv($csv, 0, $csvSeparator);
          $lineNr = 1;
          while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE) {
            $lineNr++;
            if (!isset($line[2]) || ($this->getRealType($line[2]) === 'undefined')) {
              $formState->setErrorByName('file', $this->t('The given type %type on row @row is not valid.', [
                '%type' => $line[2],
                '@row' => $lineNr,
              ]));
            }
          }
        }
      }
    }
  }

}
