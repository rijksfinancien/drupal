<?php

namespace Drupal\minfin_general\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for the open data page.
 *
 * @package Drupal\minfin_general\Form
 */
class OpenDataForm extends ConfigFormBase {

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * BannerSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory to load the settings from the config.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager to load the file storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configFactory);
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_general_open_data_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Load config.
    $config = $this->config('minfin_general.open_data.settings');

    // Get config.
    $opendata = $config->get('open_data_block');

    $form['opendata']['banner_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Header image'),
      '#upload_location' => 'public://images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
      '#default_value' => $opendata['banner_image'] ?? '',
      '#required' => TRUE,
    ];

    // Default.
    $form['opendata']['main_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Main Title'),
      '#default_value' => $opendata['main_title'],
      '#maxlength' => 128,
      '#required' => TRUE,

    ];
    for ($i = 1; $i < 4; $i++) {
      $form['opendata']['column_' . $i] = [
        '#type' => 'details',
        '#title' => $this->t('Column') . $i,
        '#open' => TRUE,
        '#tree' => TRUE,
      ];

      $form['opendata']['column_' . $i]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title') . $i,
        '#default_value' => $opendata['column_' . $i]['title'],
        '#maxlength' => 128,
        '#required' => TRUE,
      ];

      $entity = '';
      if (isset($opendata['column_' . $i]['url'])) {
        $entity = $this->nodeStorage->load($opendata['column_' . $i]['url']);
      }
      $form['opendata']['column_' . $i]['url'] = [
        '#title' => $this->t('Link') . $i,
        '#type' => 'entity_autocomplete',
        '#target_type' => 'node',
        '#default_value' => $entity ?? '',
      ];

      $form['opendata']['column_' . $i]['description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Description') . $i,
        '#default_value' => $opendata['column_' . $i]['description'],
        '#maxlength' => 255,
        '#required' => TRUE,
      ];

      $form['opendata']['column_' . $i]['image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Banner image') . $i,
        '#upload_location' => 'public://images/',
        '#upload_validators' => [
          'file_validate_extensions' => ['png gif jpg jpeg svg'],
        ],
        '#default_value' => $opendata['column_' . $i]['image'] ?? '',
        '#required' => TRUE,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('minfin_general.open_data.settings');

    // Only get the values we need, so we can loop through them.
    $values = $form_state->cleanValues()->getValues();
    for ($i = 1; $i < 4; $i++) {
      // Save images as permanent.
      if (isset($values['column_' . $i]['image'][0])) {
        /** @var \Drupal\file\FileInterface $file */
        if ($file = $this->fileStorage->load($values['column_' . $i]['image'][0])) {
          $file->setPermanent();
          $file->save();
        }
      }
    }

    if (isset($values['banner_image'][0])) {
      /** @var \Drupal\file\FileInterface $file */
      if ($file = $this->fileStorage->load($values['banner_image'][0])) {
        $file->setPermanent();
        $file->save();
      }
    }
    // Set data to config.
    $config->set('open_data_block', $values);

    // Save config.
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['minfin_general.open_data.settings'];
  }

}
