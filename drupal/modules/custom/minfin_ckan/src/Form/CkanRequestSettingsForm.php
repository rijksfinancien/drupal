<?php

namespace Drupal\minfin_ckan\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * CkanRequest settings form.
 */
class CkanRequestSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['minfin_ckan.request.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minfin_ckan_request_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load config.
    $config = $this->config('minfin_ckan.request.settings');

    $form['ckan_api'] = [
      '#type' => 'details',
      '#title' => $this->t('CKAN API Settings'),
      '#open' => TRUE,
    ];

    $form['ckan_api']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('CKAN URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('url'),
      '#description' => $this->t('The complete base URL to the CKAN API including "api/3/action/".'),
    ];

    $form['ckan_api']['filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Filters'),
    ];

    $form['ckan_api']['filters']['data_owner'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Data owner URI'),
      '#required' => TRUE,
      '#default_value' => $config->get('data_owner'),
    ];

    $form['ckan_api']['filters']['landing_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Landing page filter'),
      '#required' => FALSE,
      '#default_value' => $config->get('landing_page'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('minfin_ckan.request.settings');
    $config->set('url', $form_state->getValue('url'));
    $config->set('data_owner', $form_state->getValue('data_owner'));
    $config->set('landing_page', $form_state->getValue('landing_page'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
