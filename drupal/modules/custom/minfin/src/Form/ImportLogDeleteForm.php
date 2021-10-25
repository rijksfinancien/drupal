<?php

namespace Drupal\minfin\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\minfin\MinfinSourceFileServiceInterface;
use League\Container\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a log entry.
 */
class ImportLogDeleteForm extends FormBase {

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The minfin source file service.
   *
   * @var \Drupal\minfin\MinfinSourceFileServiceInterface
   */
  protected $minfinSourceFileService;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   * @param \Drupal\minfin\MinfinSourceFileServiceInterface $minfinSourceFileService
   *   The minfin source file service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, Connection $connection, MinfinSourceFileServiceInterface $minfinSourceFileService) {
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->connection = $connection;
    $this->minfinSourceFileService = $minfinSourceFileService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('minfin.source_file'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'import_log_delete_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string|null $logId
   *   The log id.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $logId = NULL): array {
    if (!$logId) {
      throw new NotFoundException();
    }

    $form_state->set('logId', $logId);

    $record = $this->connection->select('mf_log', 'l')
      ->fields('l', ['name', 'type', 'year', 'sub_type', 'fid'])
      ->condition('id', $logId, '=')
      ->execute()->fetchAssoc();

    $file = $this->minfinSourceFileService->getLastSourceFile($record['type'], $record['sub_type'], (int) $record['year']);
    if ($file && (int) $file->id() === (int) $record['fid']) {
      $form['text'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t("Can't remove this import log as the source file could be used in download options."),
      ];
    }
    else {
      $form['text'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Are you sure you want to delete the log %title? This action cannot be undone.', [
          '%title' => $record['name'],
        ]),
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
      ];
    }

    $form['cancel'] = [
      '#title' => $this->t('Cancel'),
      '#type' => 'link',
      '#url' => Url::fromRoute('minfin.import_log'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('minfin.import_log');
    $logId = $form_state->get('logId');

    $fid = $this->connection->select('mf_log', 'l')
      ->fields('l', ['fid'])
      ->condition('id', $logId, '=')
      ->execute()->fetchField();
    if ($file = $this->fileStorage->load($fid)) {
      $file->delete();
    };

    $this->connection->delete('mf_log')
      ->condition('id', $logId, '=')
      ->execute();

    $this->connection->delete('mf_log_message')
      ->condition('mf_log_id', $logId, '=')
      ->execute();
  }

}
