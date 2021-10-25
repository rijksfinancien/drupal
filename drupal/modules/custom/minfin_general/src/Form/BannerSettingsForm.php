<?php

namespace Drupal\minfin_general\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for the banner.
 */
class BannerSettingsForm extends ConfigFormBase {

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

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
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_general_banner_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Load config.
    $config = $this->config('minfin_general.banner.settings');

    // Get config.
    $default = $config->get('default');

    // Default.
    $form['default'] = [
      '#type' => 'details',
      '#title' => $this->t('Default'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    // Banner image field.
    $form['default']['banner_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Banner image'),
      '#upload_location' => 'public://images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
      '#default_value' => $default['banner_image'] ?? '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('minfin_general.banner.settings');

    // Only get the values we need, so we can loop through them.
    $values = $form_state->cleanValues()->getValues();

    // Get config.
    $default = $config->get('default');

    // Save images as permanent.
    if (isset($values['default']['banner_image'][0]) && $values['default']['banner_image'] !== $default['banner_image']) {
      /** @var \Drupal\file\FileInterface $file */
      if ($file = $this->fileStorage->load($values['default']['banner_image'][0])) {
        $file->setPermanent();
        $file->save();
      }
    }

    // Set data to config.
    $config->set('default', $values['default']);

    // Save config.
    $config->save();
    Cache::invalidateTags(['minfin:bannerImage']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['minfin_general.banner.settings'];
  }

}
