<?php

namespace Drupal\minfin\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to create/edit a ISB kamerstuk title record.
 */
class KamerstukIsbTitleForm extends FormBase {

  /**
   * The connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_kamerstuk_isb_title_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['year'] = [
      '#type' => 'number',
      '#title' => $this->t('Year'),
      '#required' => TRUE,
      '#min' => 0,
    ];

    $form['phase'] = [
      '#type' => 'number',
      '#title' => $this->t('Phase'),
      '#required' => TRUE,
      '#min' => 0,
    ];

    $form['hoofdstuk_minfin_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hoofdstuk id'),
      '#required' => TRUE,
      '#maxlength' => 8,
      '#size' => 8,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
    ];

    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#date_date_format' => 'd-m-Y',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $fields = [
      'naam' => $form_state->getValue('name'),
    ];

    if ($date = $form_state->getValue('date')) {
      $fields['date'] = date('Y-m-d\TH:i:s\Z', strtotime($date));
    }

    $this->connection->merge('mf_kamerstuk_isb_title')
      ->keys([
        'jaar' => $form_state->getValue('year'),
        'fase' => 'ISB' . $form_state->getValue('phase'),
        'hoofdstuk_minfin_id' => $form_state->getValue('hoofdstuk_minfin_id'),
      ])
      ->fields($fields)
      ->execute();

    $params = [
      '%title' => $form_state->getValue('name'),
      '@jaar' => $form_state->getValue('year'),
      '@fase' => 'ISB' . $form_state->getValue('phase'),
      '@hoofdstuk_minfin_id' => $form_state->getValue('hoofdstuk_minfin_id'),
    ];
    $this->messenger()->addStatus($this->t('ISB title %title for @fase @jaar @hoofdstuk_minfin_id has been saved', $params));
  }

}
