<?php

namespace Drupal\minfin\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the ImportError entity.
 *
 * @ingroup minfin
 *
 * @ContentEntityType(
 *   id = "import_error",
 *   label = @Translation("Import error"),
 *   label_singular = @Translation("Import error"),
 *   label_plural = @Translation("Import errors"),
 *   label_count = @PluralTranslation(
 *     singular = "@count import error",
 *     plural = "@count import errors"
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
 *   base_table = "mf_import_error",
 *   data_table = "mf_import_error_field_data",
 *   admin_permission = "view import_log",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class ImportError extends ContentEntityBase implements ImportErrorInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSeverity(): int {
    return (int) $this->get('severity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSeverityName(): string {
    $severity = (int) $this->get('severity')->value;
    if ($severity === self::SEVERITY_ERROR) {
      return $this->t('Error');
    }
    if ($severity === self::SEVERITY_WARNING) {
      return $this->t('Warning');
    }
    if ($severity === self::SEVERITY_CHANGED) {
      return $this->t('Changed');
    }
    if ($severity === self::SEVERITY_SKIPPED) {
      return $this->t('Skipped');
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage(): string {
    return $this->get('message')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getLineNumber(): ?int {
    return (int) $this->get('line')->value ?: NULL;
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

    $fields['severity'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Severity'))
      ->setSettings([
        'allowed_values' => [
          self::SEVERITY_ERROR => t('Error'),
          self::SEVERITY_WARNING => t('Warning'),
          self::SEVERITY_CHANGED => t('Changed'),
          self::SEVERITY_SKIPPED => t('Skipped'),
        ],
      ])
      ->setRequired(TRUE);

    $fields['message'] = BaseFieldDefinition::create('text')
      ->setLabel(t('Message'))
      ->setRequired(TRUE);

    $fields['line'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Line'))
      ->setRequired(FALSE);

    return $fields;
  }

}
