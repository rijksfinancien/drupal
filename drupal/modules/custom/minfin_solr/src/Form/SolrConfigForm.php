<?php

namespace Drupal\minfin_solr\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\minfin_solr\SolrClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the configuration form for all SOLR settings.
 */
class SolrConfigForm extends ConfigFormBase {

  /**
   * The SOLR client for testing the settings.
   *
   * @var \Drupal\minfin_solr\SolrClientInterface
   */
  protected $solrClient;

  /**
   * Constructs a SolrConfigForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\minfin_solr\SolrClientInterface $solrClient
   *   The minfin SOLR client service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, SolrClientInterface $solrClient) {
    parent::__construct($configFactory);
    $this->solrClient = $solrClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('minfin_solr.solr_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_solr_solr_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['minfin_solr.solr.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('minfin_solr.solr.settings');

    foreach ($this->getMethods() as $method) {
      $form[$method] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Endpoint: @method', ['@method' => $method]),
      ];
      $form[$method][$method . '_host'] = [
        '#type' => 'url',
        '#title' => $this->t('Host'),
        '#default_value' => $config->get($method . '_host'),
        '#description' => $this->t('The host of the SOLR environment. For example: https://minfin.textinfo.nl'),
        '#required' => TRUE,
      ];
      $form[$method][$method . '_path'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Path'),
        '#default_value' => $config->get($method . '_path'),
        '#description' => $this->t('The path of the SOLR environment. For example: solr'),
        '#required' => FALSE,
      ];
      $form[$method]['core'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('SOLR cores'),
        '#description' => $this->t('The core of the SOLR environment. For example: minfin_search'),
      ];
      foreach ($this->getCores() as $core) {
        $form[$method]['core'][$method . '_' . $core . '_core'] = [
          '#type' => 'textfield',
          '#title' => $this->t('@type core', ['@type' => $core]),
          '#default_value' => $config->get($method . '_' . $core . '_core'),
          '#required' => TRUE,
        ];
      }
    }

    $form['authorisation'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Authorisation'),
    ];

    $form['authorisation']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('The password that will be used for authentication by the SOLR environment.'),
      '#default_value' => $config->get('username'),
      '#required' => FALSE,
    ];

    $form['authorisation']['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#description' => $this->t('The password that will be used for authentication by the SOLR environment.'),
      '#default_value' => $config->get('password'),
      '#required' => FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('minfin_solr.solr.settings');
    if ($form_state->getValue('username') && !($form_state->getValue('password') || $config->get('password'))) {
      $form_state->setErrorByName('password', $this->t('When settings a username you must also give a password.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('minfin_solr.solr.settings');

    foreach ($this->getMethods() as $method) {
      $config->set($method . '_host', $form_state->getValue($method . '_host'));
      $config->set($method . '_path', $form_state->getValue($method . '_path'));
      foreach ($this->getCores() as $core) {
        $config->set($method . '_' . $core . '_core', $form_state->getValue($method . '_' . $core . '_core'));
      }
    }

    if ($username = $form_state->getValue('username')) {
      $config->set('username', $username);
      if ($password = $form_state->getValue('password')) {
        $config->set('password', $password);
      }
    }
    else {
      $config->set('username', NULL);
      $config->set('password', NULL);
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Retrieve a list of available Solr methods.
   *
   * @return string[]
   *   The methods.
   */
  protected function getMethods(): array {
    return ['update', 'search'];
  }

  /**
   * Retrieve a list of available Solr cores.
   *
   * @return string[]
   *   The cores.
   */
  protected function getCores(): array {
    return ['search', 'wie_ontvingen'];
  }

}
