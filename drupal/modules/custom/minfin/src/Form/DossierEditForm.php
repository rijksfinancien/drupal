<?php

namespace Drupal\minfin\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use League\Container\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for editing a dossier entry.
 */
class DossierEditForm extends FormBase {

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
    return 'mf_dossier_edit_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string|null $dossierId
   *   The dossier id.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $dossierId = NULL): array {
    if (!$dossierId) {
      throw new NotFoundException();
    }

    $form_state->set('dossierId', $dossierId);

    $record = $this->connection->select('mf_kamerstuk_dossier', 'kd')
      ->fields('kd', ['type', 'jaar', 'fase', 'hoofdstuk_minfin_id', 'dossier_number'])
      ->condition('kamerstuk_dossier_id', $dossierId, '=')
      ->execute()->fetchAssoc();

    $form['type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Type'),
      '#value' => $record['type'],
      '#disabled' => TRUE,
    ];

    $form['jaar'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Year'),
      '#value' => $record['jaar'],
      '#disabled' => TRUE,
    ];

    $form['fase'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phase'),
      '#value' => $record['fase'],
      '#disabled' => TRUE,
    ];

    $form['hoofdstuk_minfin_id'] = [
      '#type' => 'textfield',
      '#title' => 'Hoofdstuk Id',
      '#value' => $record['hoofdstuk_minfin_id'],
      '#disabled' => TRUE,
    ];

    $form['code'] = [
      '#type' => 'number',
      '#title' => $this->t('Dossier number'),
      '#required' => TRUE,
      '#default_value' => $record['dossier_number'],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    $form['cancel'] = [
      '#title' => $this->t('Cancel'),
      '#type' => 'link',
      '#url' => Url::fromRoute('minfin.mf_dossier.collection'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->connection->update('mf_kamerstuk_dossier')
      ->fields(['dossier_number' => $form_state->getValue('code')])
      ->condition('kamerstuk_dossier_id', $form_state->get('dossierId'), '=')
      ->execute();

    $form_state->setRedirect('minfin.mf_dossier.collection');
  }

}
