<?php

namespace Drupal\minfin\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use League\Container\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a kamerstuk ISB title record.
 */
class KamerstukIsbTitleDeleteForm extends FormBase {

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
    return 'minfin_kamerstuk_isb_title_delete_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string|null $jaar
   *   The year.
   * @param string|null $fase
   *   The phase.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $jaar = NULL, ?string $fase = NULL, ?string $hoofdstukMinfinId = NULL): array {
    if (!$jaar || !$fase || !$hoofdstukMinfinId) {
      throw new NotFoundException();
    }

    $form_state->set('jaar', $jaar);
    $form_state->set('fase', $fase);
    $form_state->set('hoofdstukMinfinId', $hoofdstukMinfinId);

    $form['text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Are you sure you want to delete the record "%jaar %fase %hoofdstukMinfinId"? This action cannot be undone.', [
        '%jaar' => $jaar,
        '%fase' => $fase,
        '%hoofdstukMinfinId' => $hoofdstukMinfinId,
      ]),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
    ];

    $form['cancel'] = [
      '#title' => $this->t('Cancel'),
      '#type' => 'link',
      '#url' => Url::fromRoute('minfin.kamerstuk_isb_title'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('minfin.kamerstuk_isb_title');
    $jaar = $form_state->get('jaar');
    $fase = $form_state->get('fase');
    $hoofdstukMinfinId = $form_state->get('hoofdstukMinfinId');

    $this->connection->delete('mf_kamerstuk_isb_title')
      ->condition('jaar', $jaar, '=')
      ->condition('fase', $fase, '=')
      ->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
      ->execute();

  }

}
