<?php

namespace Drupal\minfin\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the ImportLog entity.
 *
 * @ingroup minfin
 *
 * @ContentEntityType(
 *   id = "import_log",
 *   label = @Translation("Import log"),
 *   label_singular = @Translation("Import log"),
 *   label_plural = @Translation("Import logs"),
 *   label_collection = @Translation("Import logs"),
 *   label_count = @PluralTranslation(
 *     singular = "@count import log",
 *     plural = "@count import logs"
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "mf_import_log",
 *   data_table = "mf_import_log_field_data",
 *   admin_permission = "view import_log",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "delete-form" = "/import_log/{import_log}/delete",
 *   },
 * )
 */
class ImportLog extends ContentEntityBase implements ImportLogInterface {

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->get('type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getYear(): int {
    return (int) $this->get('year')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPhase(): string {
    return $this->get('phase')->value ?? '-';
  }

  /**
   * {@inheritdoc}
   */
  public function getFilename(): ?string {
    return $this->get('filename')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getImported(): int {
    return (int) $this->get('imported')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeleted(): int {
    return (int) $this->get('deleted')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function increaseImported(int $amount = 1): int {
    $this->set('imported', $this->getImported() + $amount);
    return $this->getImported();
  }

  /**
   * {@inheritdoc}
   */
  public function increaseDeleted(int $amount = 1): int {
    $this->set('deleted', $this->getDeleted() + $amount);
    return $this->getDeleted();
  }

  /**
   * {@inheritdoc}
   */
  public function logError(ImportErrorInterface $importError) {
    $this->get('import_errors')->appendItem($importError);
  }

  /**
   * {@inheritdoc}
   */
  public function getLogErrors(): array {
    return $this->get('import_errors')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType): array {
    $fields = parent::baseFieldDefinitions($entityType);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setRequired(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRequired(TRUE);

    $fields['year'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Year'))
      ->setRequired(TRUE)
      ->setDefaultValue(0);

    $fields['phase'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Phase'))
      ->setRequired(TRUE)
      ->setSettings(['allowed_values' => self::getPhases()]);

    $fields['filename'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Filename'))
      ->setRequired(TRUE);

    $fields['imported'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Imported'))
      ->setRequired(TRUE)
      ->setDefaultValue(0);

    $fields['deleted'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Deleted'))
      ->setRequired(TRUE)
      ->setDefaultValue(0);

    $fields['import_errors'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Reported errors'))
      ->setRequired(FALSE)
      ->setSetting('target_type', 'import_error')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    return $fields;
  }

  /**
   * Retrieve the available phases.
   *
   * @return array
   *   The phases.
   */
  public static function getPhases(): array {
    return [
      'owb' => t('OWB'),
      'supp1' => t('1e suppletoire'),
      'supp2' => t('2e suppletoire'),
      'jv' => t('JV'),
    ];
  }

}
