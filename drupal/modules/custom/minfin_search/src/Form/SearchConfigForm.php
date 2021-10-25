<?php

namespace Drupal\minfin_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The search config form.
 */
class SearchConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_search_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['minfin_search.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('minfin_search.settings');

    $form['rbv_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL to rbv site.'),
      '#default_value' => $config->get('rbv_url'),
      '#description' => $this->t('The url to the rbv site.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('minfin_search.settings');
    $url = $form_state->getValue('rbv_url');
    $config->set('rbv_url', ((substr($url, -1) === '/') ? substr($url, 0, -1) : $url));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
