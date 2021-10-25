<?php

namespace Drupal\minfin\Form\Import;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\minfin\MinfinNamingServiceInterface;
use Drupal\minfin\MinfinServiceInterface;
use Drupal\minfin\SolrKamerstukClientInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the base definition for import forms.
 */
abstract class ImportBaseForm extends FormBase {

  /**
   * An error occured which is definetly not correct.
   *
   * For example a required field was empty, or a column that should contain a
   * number contained a letter instead.
   */
  public const SEVERITY_ERROR = 1;

  /**
   * Something odd was noticed during the import and we assume its not correct.
   */
  public const SEVERITY_WARNING = 2;

  /**
   * An error occured during the import, but we could automatically fix it.
   *
   * For example the "artikel nummer" which should be an integer was written as
   * a decimal, in which case the decimal part can be removed as it usually
   * indicates a "artikel onderverdeling". Or the column containing the amount
   * was written with a euro sign, in which case we stripped the value of it.
   */
  public const SEVERITY_CHANGED = 3;

  /**
   * A line that was not imported because the logic told us.
   */
  public const SEVERITY_SKIPPED = 4;

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
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * The file usage.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The SOLR kamerstuk client.
   *
   * @var \Drupal\minfin\SolrKamerstukClientInterface
   */
  protected $solrKamersukClient;

  /**
   * The minfin naming service.
   *
   * @var \Drupal\minfin\MinfinNamingServiceInterface
   */
  protected $minfinNaming;

  /**
   * The minfin service.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Id of the current import log.
   *
   * @var int|null
   */
  protected $log;

  /**
   * The amount of rows skipped.
   *
   * @var int|null
   */
  protected $rowSkipped;

  /**
   * The amount of rows imported.
   *
   * @var int|null
   */
  protected $rowsImported;

  /**
   * The cache tag invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs an ImportBaseForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   The file system.
   * @param \Drupal\file\FileUsage\FileUsageInterface $fileUsage
   *   The file usage.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   * @param \Drupal\minfin\SolrKamerstukClientInterface $solrKamersukClient
   *   The minfin SOLR kamerstuk client.
   * @param \Drupal\minfin\MinfinNamingServiceInterface $minfinNaming
   *   The minfin naming service.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   The minfin service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   The cache tag invalidator.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, Connection $connection, FileSystem $fileSystem, FileUsageInterface $fileUsage, ClientInterface $httpClient, SolrKamerstukClientInterface $solrKamersukClient, MinfinNamingServiceInterface $minfinNaming, MinfinServiceInterface $minfinService, QueueFactory $queueFactory, TimeInterface $time, CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->connection = $connection;
    $this->fileSystem = $fileSystem;
    $this->fileUsage = $fileUsage;
    $this->httpClient = $httpClient;
    $this->solrKamersukClient = $solrKamersukClient;
    $this->minfinNaming = $minfinNaming;
    $this->minfinService = $minfinService;
    $this->queueFactory = $queueFactory;
    $this->time = $time;
    $this->cacheTagsInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('file_system'),
      $container->get('file.usage'),
      $container->get('http_client'),
      $container->get('minfin.solr_kamerstuk'),
      $container->get('minfin.naming'),
      $container->get('minfin.minfin'),
      $container->get('queue'),
      $container->get('datetime.time'),
      $container->get('cache_tags.invalidator'),
    );
  }

  /**
   * Retrieve the type of the import.
   *
   * @return string
   *   The import type.
   */
  abstract protected function getImportType(): string;

  /**
   * Retrieve the file types of the import.
   *
   * @return array
   *   The import file types.
   */
  abstract protected function getImportFileTypes(): array;

  /**
   * The custom form submission handler.
   *
   * @param \Drupal\file\FileInterface $file
   *   The uploaded file.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   *
   * @return bool
   *   Whether or not the import was successful.
   */
  abstract protected function submit(FileInterface $file, FormStateInterface $formState): bool;

  /**
   * The amount of columns that are required for a correctly formatted file.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return int|null
   *   The amount of required columns or NULL if no check is required.
   */
  protected function getColumnCount(FormStateInterface $formState): ?int {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Import name'),
      '#required' => TRUE,
      '#weight' => 0,
    ];

    $form['year'] = [
      '#type' => 'number',
      '#title' => $this->t('Year'),
      '#required' => TRUE,
      '#min' => 1900,
      '#default_value' => date('Y'),
      '#weight' => 10,
    ];

    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('File'),
      '#required' => TRUE,
      '#upload_location' => 'private://import/',
      '#upload_validators' => [
        'file_validate_extensions' => [implode(' ', $this->getImportFileTypes())],
      ],
      '#limit_validation_errors' => [],
      '#progress_message' => $this->t('Uploading file...'),
      '#weight' => 49,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 50,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import data'),
    ];

    $form['actions']['documentation'] = [
      '#type' => 'link',
      '#title' => $this->t('Show documentation'),
      '#url' => Url::fromRoute('minfin.importer.documentation', ['type' => $this->getImportType()], [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
          'data-dialog-renderer' => 'off_canvas',
          'data-dialog-options' => Json::encode(['width' => 600]),
        ],
      ]),
      '#attached' => [
        'library' => [
          'core/drupal.dialog.ajax',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $formState): void {
    if ($formState->getTriggeringElement()['#id'] !== 'edit-submit') {
      return;
    }

    if ($columnsRequired = $this->getColumnCount($formState)) {
      $file = NULL;
      $fileId = $formState->getValue('file', []);
      if ($fileId = reset($fileId)) {
        /** @var \Drupal\file\FileInterface $file */
        $file = $this->fileStorage->load($fileId);
      }

      if ($file) {
        $csvSeparator = $this->determineFileSeparator($file->getFileUri(), $this->getColumnCount($formState));
        if ($csv = fopen($file->getFileUri(), 'rb')) {
          $counter = 0;
          while (($line = fgetcsv($csv, 0, $csvSeparator)) !== FALSE && $counter <= 20) {
            $columnsFound = count($line);
            if ($columnsFound > 0 && $columnsFound < $columnsRequired) {
              $formState->setErrorByName('', $this->t("The uploaded file has less than @count columns. This file either doesn't match the required format, or isn't a valid CSV file (seperated by comma).", ['@count' => $columnsRequired]));
            }
            $counter++;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $file = NULL;
    $fileId = $formState->getValue('file', []);
    if ($fileId = reset($fileId)) {
      /** @var \Drupal\file\Entity\File $file */
      $file = $this->fileStorage->load($fileId);
      $file->setPermanent();
      $file->save();
    }
    if (!$file) {
      $this->messenger()->addError($this->t('Failed to load file object.'));
      return;
    }

    // Create import log entity.
    try {
      $this->log = $this->connection->insert('mf_log')
        ->fields([
          'created' => $this->time->getCurrentTime(),
          'type' => $this->getImportType(),
          'sub_type' => $formState->getValue('phase') ?? $formState->getValue('sub_type'),
          'name' => $formState->getValue('name'),
          'year' => (int) $formState->getValue('year'),
          'fid' => $file->id(),
          'uid' => $this->currentUser()->id(),
        ])
        ->execute();
      $this->rowsImported = 0;
      $this->rowSkipped = 0;
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Failed to create log record.'));
      return;
    }

    $this->cacheTagsInvalidator->invalidateTags(['minfin_import:' . $this->getImportType()]);

    // Import the file.
    $succesfullImport = $this->submit($file, $formState);

    // Update the import log status.
    try {
      $fields = [
        'rows_imported' => $this->rowsImported,
        'rows_skipped' => $this->rowSkipped,
      ];
      if ($succesfullImport) {
        $this->messenger()->addMessage($this->t('Successfully imported file %file.', ['%file' => $formState->getValue('name')]));
        $fields['state'] = 1;
      }
      else {
        $this->messenger()->addMessage($this->t('Something went wrong during the import.'));
        $fields['state'] = 2;
      }

      $query = $this->connection->update('mf_log');
      $query->condition('id', $this->log, '=');
      $query->fields($fields);
      $query->execute();
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Failed to create log record.'));
      return;
    }
  }

  /**
   * Helper function to log an error to the log entity.
   *
   * @param int $severity
   *   The severity.
   * @param string $message
   *   The actual error message.
   * @param array $args
   *   The arguments for the error message.
   * @param int|null $line
   *   If known the line on which the error occured.
   */
  protected function logError(int $severity, $message, array $args = [], int $line = NULL): void {
    if (!$this->log) {
      return;
    }

    try {
      $fields = [
        'mf_log_id' => $this->log,
        'severity' => $severity,
        'message' => $message,
        'variables' => serialize($args),
      ];
      if ($line) {
        $fields['line'] = $line;
      }

      $this->connection->insert('mf_log_message')
        ->fields($fields)
        ->execute();
    }
    catch (\Exception $e) {
      return;
    }
  }

  /**
   * Insert a new 'hoofdstuk' into the database.
   *
   * @param string $minfinId
   *   The minfin 'hoofdstuk' id.
   * @param string $naam
   *   The name of the 'hoofdstuk'.
   * @param int $jaar
   *   The year this 'hoofdstuk' is active.
   *
   * @return int|null
   *   The 'hoofdstuk' id or null.
   */
  public function insertHoofdstuk(string $minfinId, string $naam, int $jaar): ?int {
    $hoofdstukId = $this->connection->select('mf_hoofdstuk', 'h')
      ->fields('h', ['hoofdstuk_id'])
      ->condition('hoofdstuk_minfin_id', $minfinId, '=')
      ->condition('jaar', $jaar, '=')
      ->execute()
      ->fetchField();

    // Insert a new record if we haven't gotten a chapter with that id yet.
    if (!$hoofdstukId) {
      try {
        $fields = [
          'hoofdstuk_minfin_id' => $minfinId,
          'jaar' => $jaar,
          'naam' => $naam,
        ];

        $query = $this->connection->insert('mf_hoofdstuk');
        $query->fields($fields);
        return $query->execute();
      }
      catch (\Exception $e) {
        return NULL;
      }
    }

    return $hoofdstukId;
  }

  /**
   * Insert a new 'artikel' into the database.
   *
   * @param string $minfinId
   *   The minfin 'artikel' id.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string $naam
   *   The name of the 'artikel'.
   * @param int $jaar
   *   The year this 'artikel' is active.
   * @param int $lineNr
   *   The line number.
   *
   * @return int|null
   *   The 'artikel' id or null.
   */
  public function insertArtikel(string $minfinId, string $hoofdstukMinfinId, string $naam, int $jaar, int $lineNr = NULL): ?int {
    if (!is_numeric($minfinId)) {
      $message = $this->t("The given articlenumber wasn't valid.");
      $this->logError(self::SEVERITY_ERROR, $message, [], $lineNr);
      return NULL;
    }

    $artikelId = $this->connection->select('mf_artikel', 'a')
      ->fields('a', ['artikel_id'])
      ->condition('hoofdstuk_minfin_id', $hoofdstukMinfinId, '=')
      ->condition('artikel_minfin_id', $minfinId, '=')
      ->condition('jaar', $jaar, '=')
      ->execute()
      ->fetchField();

    // Insert a new record if we haven't gotten a artikel with that id yet.
    if (!$artikelId) {
      try {
        $fields = [
          'hoofdstuk_minfin_id' => $hoofdstukMinfinId,
          'artikel_minfin_id' => $minfinId,
          'jaar' => $jaar,
          'naam' => $naam,
        ];

        $query = $this->connection->insert('mf_artikel');
        $query->fields($fields);
        return $query->execute();
      }
      catch (\Exception $e) {
        return NULL;
      }
    }

    return $artikelId;
  }

  /**
   * Cleanup a row from a csv file.
   *
   * @param string[] $row
   *   The row to be cleaned.
   * @param string|null $encoding
   *   The file encoding.
   */
  protected function cleanupImportRow(array &$row, ?string $encoding = NULL): void {
    foreach ($row as &$v) {
      $v = str_replace(["\r\n", "\n\r", "\n", "\r", '_x000D_'], '', $v);
      $v = preg_replace('/\s+/', ' ', $v);
      $v = trim($v);

      if ($encoding && $encoding === 'ISO-8859-1') {
        $v = mb_convert_encoding($v, 'UTF-8', $encoding);
      }
    }
  }

  /**
   * Clean up the currency numbers so that the output is a valid integer.
   *
   * @param string|float|int $value
   *   The value that needs to be cleaned up.
   * @param int|null $lineNr
   *   If known the line on which the value was found.
   * @param string|null $column
   *   If known the column on which the value was found.
   *
   * @return int
   *   The cleaned up version of $value.
   */
  protected function fixCurrencyValues($value, int $lineNr = NULL, $column = NULL): int {
    if (($periodCount = substr_count($value, '.')) || !is_numeric($value)) {
      // In some cases the value is being contaminated by common characters,
      // which we can easily fix. Such as an Euro sign or a comma.
      $returnValue = trim(str_replace(['€', ','], ['', ''], $value));

      if ($periodCount > 1) {
        // If there are more than 1 periods, we can assume its being used as a
        // thousend seperator instead of a decimal pointer. So we can simply
        // remove it.
        $returnValue = trim(str_replace('.', '', $value));
      }
      elseif ($periodCount === 1) {
        $args = ['%value' => $value];
        $message = 'Are you sure the value %value is correct? A period in a number is used to indicate a decimal.';
        if ($column) {
          $args['@column'] = $column;
          $message = 'Are you sure the value %value in column @column is correct? A period in a number is used to indicate a decimal.';
        }
        $this->logError(self::SEVERITY_WARNING, $message, $args, $lineNr);

        return $value;
      }

      if (empty($returnValue)) {
        $args = ['%return' => 0];
        $message = 'The value was empty so it has been changed to %return';
        if ($column) {
          $args['@column'] = $column;
          $message = 'The value in column @column was empty so it has been changed to %return';
        }
        $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);

        return 0;
      }
      elseif (is_numeric($returnValue)) {
        $args = ['%value' => $value, '%return' => $returnValue];
        $message = "The value %value isn't a valid integer so the value has been changed to %return";
        if ($column) {
          $args['@column'] = $column;
          $message = "The value %value in column @column isn't a valid integer so the value has been changed to %return";
        }
        $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);

        return $returnValue;
      }
      else {
        $args = ['%value' => $value, '%return' => 0];
        $message = "The value %value isn't a valid integer so the value has been changed to %return";
        if ($column) {
          $args['@column'] = $column;
          $message = "The value %value in column @column isn't a valid integer so the value has been changed to %return";
        }
        $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);

        return 0;
      }
    }

    return $returnValue ?? $value;
  }

  /**
   * Helper function to correct the phase string.
   *
   * @param string $value
   *   The value that needs to be corrected.
   *
   * @return string
   *   Returns the corrected value or the original input.
   */
  protected function fixPhase(string $value): string {
    $replace = [
      'OW' => 'OWB',
    ];
    return $replace[strtoupper($value)] ?? strtoupper($value);
  }

  /**
   * Helper function to make sure we only import consistent 'hoofdstuk' numbers.
   *
   * @param string $value
   *   The value that needs to be corrected.
   * @param int|null $lineNr
   *   If known the line on which the value was found.
   *
   * @return string
   *   Returns the corrected value or the original input.
   */
  protected function fixHoofdstuk(string $value, int $lineNr = NULL): string {
    $replace = [
      '1' => 'I',
      '2a' => 'IIA',
      '2b' => 'IIB',
      '3' => 'III',
      '4' => 'IV',
      '5' => 'V',
      '6' => 'VI',
      '7' => 'VII',
      '8' => 'VIII',
      '9' => 'IX',
      '9a' => 'IXA',
      '9b' => 'IXB',
      '10' => 'X',
      '11' => 'XI',
      '12' => 'XII',
      '13' => 'XIII',
      '14' => 'XIV',
      '15' => 'XV',
      '16' => 'XVI',
      '17' => 'XVII',
      '18' => 'XVIII',
    ];
    $returnValue = $replace[strtolower($value)] ?? $value;

    if ($returnValue !== $value) {
      $args = ['%value' => $value, '%return' => $returnValue];
      $message = "The value %value isn't a valid chapter id so the value has been changed to %return";
      $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);
    }

    return $returnValue;
  }

  /**
   * Helper function to make sure we only import valid 'artikel' numbers.
   *
   * @param string $value
   *   The value that needs to be validated.
   * @param int|null $lineNr
   *   If known the line on which the value was found.
   *
   * @return null|int
   *   Return NULL on invalid 'artikel' otherwise send back the original value.
   */
  protected function fixArtikel($value, int $lineNr = NULL): ?int {
    // If its a valid number return the original value.
    if (is_numeric($value)) {
      return $value;
    }

    // Check if it isn't just a sub article number, in that case we explode the
    // value on "." and return the first number.
    $explodedValue = explode('.', $value);
    if (count($explodedValue) > 1 && is_numeric($explodedValue[0])) {
      $args = ['%value' => $value, '%return' => $explodedValue[0]];
      $message = "The value %value isn't a valid article id so the value has been changed to %return";
      $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);
      return $explodedValue[0];
    }

    // Check if we can try to fix it with some intval() magic.
    $intValue = (int) $value;
    if (strpos($value, (string) $intValue) === 0) {
      $args = ['%value' => $value, '%return' => $intValue];
      $message = "The value %value isn't a valid article id so the value has been changed to %return";
      $this->logError(self::SEVERITY_CHANGED, $message, $args, $lineNr);
      return $intValue;
    }

    return NULL;
  }

  /**
   * Determine the CSV file separator.
   *
   * @param string $fileUri
   *   The location of the file.
   * @param int|null $columnCount
   *   The column count.
   *
   * @return string
   *   The CSV separator.
   */
  protected function determineFileSeparator(string $fileUri, ?int $columnCount): string {
    if ($file = fopen($fileUri, 'rb')) {
      $row = fgetcsv($file, 0, ';');
      if (count($row) >= $columnCount) {
        return ';';
      }
    }

    return ',';
  }

  /**
   * Determine the CSV file encoding.
   *
   * @param string $fileUri
   *   The location of the file.
   *
   * @return string
   *   The encoding
   */
  protected function determineFileEncoding(string $fileUri): string {
    $content = file_get_contents($fileUri);
    if ($encoding = mb_detect_encoding($content, ['ISO-8859-1', 'UTF-8', 'ASCII'])) {
      return $encoding;
    }
    return 'UTF-8';
  }

}
