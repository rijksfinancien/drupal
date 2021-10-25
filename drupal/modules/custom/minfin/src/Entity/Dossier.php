<?php

namespace Drupal\minfin\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Dossier entity.
 *
 * @ingroup minfin
 *
 * @ContentEntityType(
 *   id = "mf_dossier",
 *   label = @Translation("Dossier"),
 *   label_singular = @Translation("Dossier"),
 *   label_plural = @Translation("Dossiers"),
 *   label_collection = @Translation("Dossiers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count dossier",
 *     plural = "@count dossiers"
 *   ),
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "mf_dossier",
 *   data_table = "mf_dossier_field_data",
 *   admin_permission = "administer mf_dossier entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class Dossier extends ContentEntityBase implements DossierInterface {

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime(): int {
    return $this->get('created')->value;
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
    return $this->get('phase')->value ?: '';
  }

  /**
   * {@inheritdoc}
   */
  public function getCode(): string {
    return $this->get('code')->value;
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

    $fields['year'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Year'))
      ->setRequired(TRUE)
      ->setDefaultValue(0);

    $fields['phase'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Phase'))
      ->setRequired(TRUE)
      ->setSettings(['allowed_values' => self::getPhases()]);

    $fields['code'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Code'))
      ->setRequired(TRUE)
      ->setDefaultValue(0);

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
      'jv' => t('JV'),
    ];
  }

}
