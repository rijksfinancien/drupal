<?php

namespace Drupal\minfin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the kammerstukken.
 */
class KamerstukkenTextForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_kamerstuk_text_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('minfin.kamerstuk.text');

    foreach ($this->getPhases() as $phase => $name) {
      $form[$phase] = [
        '#type' => 'details',
        '#title' => $name,
        '#tree' => TRUE,
      ];

      $text = $config->get($phase);

      $form[$phase]['prefix'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Prefix'),
        '#format' => $text['prefix']['format'] ?? 'safe_html',
        '#default_value' => $text['prefix']['value'] ?? NULL,
      ];

      $form[$phase]['suffix'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Suffix'),
        '#format' => $text['suffix']['format'] ?? 'safe_html',
        '#default_value' => $text['suffix']['value'] ?? NULL,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('minfin.kamerstuk.text');

    foreach (array_keys($this->getPhases()) as $phase) {
      $config->set($phase, $form_state->getValue($phase));

      if ($phase === 'memorie_van_toelichting_OWB') {
        $config->set('voorstel_van_wet_OWB', $form_state->getValue($phase));
      }

      if ($phase === 'memorie_van_toelichting_1SUPP') {
        $config->set('voorstel_van_wet_1SUPP', $form_state->getValue($phase));
      }

      if ($phase === 'memorie_van_toelichting_2SUPP') {
        $config->set('voorstel_van_wet_2SUPP', $form_state->getValue($phase));
      }
    }

    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['minfin.kamerstuk.text'];
  }

  /**
   * Get the phaes.
   *
   * @return array
   *   A list with the phaeses.
   */
  private function getPhases(): ?array {
    return [
      'memorie_van_toelichting_OWB' => 'Begroting',
      'memorie_van_toelichting_1SUPP' => '1e suppletoire',
      'memorie_van_toelichting_2SUPP' => '2e suppletoire',
      'jaarverslag_JV' => 'Jaarverslag',
      'memorie_van_toelichting_JV' => 'Slotwet',
    ];
  }

}
