<?php

namespace Drupal\booking_manager\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Manage days entity entity.
 *
 * @ingroup booking_manager
 *
 * @ContentEntityType(
 *   id = "manage_days_entity",
 *   label = @Translation("Manage days entity"),
 *   bundle_label = @Translation("Manage days entity type"),
 *   handlers = {
 *     "storage" = "Drupal\booking_manager\ManageDaysEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\booking_manager\ManageDaysEntityListBuilder",
 *     "views_data" = "Drupal\booking_manager\Entity\ManageDaysEntityViewsData",
 *     "translation" = "Drupal\booking_manager\ManageDaysEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\booking_manager\Form\ManageDaysEntityForm",
 *       "add" = "Drupal\booking_manager\Form\ManageDaysEntityForm",
 *       "edit" = "Drupal\booking_manager\Form\ManageDaysEntityForm",
 *       "delete" = "Drupal\booking_manager\Form\ManageDaysEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\booking_manager\ManageDaysEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\booking_manager\ManageDaysEntityAccessControlHandler",
 *   },
 *   base_table = "manage_days_entity",
 *   data_table = "manage_days_entity_field_data",
 *   revision_table = "manage_days_entity_revision",
 *   revision_data_table = "manage_days_entity_field_revision",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer manage days entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/manage_days_entity/{manage_days_entity}",
 *     "add-page" = "/admin/structure/manage_days_entity/add",
 *     "add-form" = "/admin/structure/manage_days_entity/add/{manage_days_entity_type}",
 *     "edit-form" = "/admin/structure/manage_days_entity/{manage_days_entity}/edit",
 *     "delete-form" = "/admin/structure/manage_days_entity/{manage_days_entity}/delete",
 *     "version-history" = "/admin/structure/manage_days_entity/{manage_days_entity}/revisions",
 *     "revision" = "/admin/structure/manage_days_entity/{manage_days_entity}/revisions/{manage_days_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/manage_days_entity/{manage_days_entity}/revisions/{manage_days_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/manage_days_entity/{manage_days_entity}/revisions/{manage_days_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/manage_days_entity/{manage_days_entity}/revisions/{manage_days_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/manage_days_entity",
 *   },
 *   bundle_entity_type = "manage_days_entity_type",
 *   field_ui_base_route = "entity.manage_days_entity_type.edit_form"
 * )
 */
class ManageDaysEntity extends EditorialContentEntityBase implements ManageDaysEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   *
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id()
    ];
  }

  /**
   *
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the manage_days_entity owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   *
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   *
   * @return []
   */
  public function getCreneau() {
    return [
      'value' => $this->get('creneau')->value,
      'end_value' => $this->get('creneau')->end_value
    ];
  }

  /**
   *
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the Manage days entity entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setTranslatable(TRUE)->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'author',
      'weight' => 0
    ])->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Name'))->setDescription(t('The name of the Manage days entity entity.'))->setRevisionable(TRUE)->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);

    $fields['creneau'] = BaseFieldDefinition::create('daterange')->setLabel(t('Creneau'))->setRevisionable(TRUE)->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    $fields['creneau_string'] = BaseFieldDefinition::create('string')->setLabel(t('Creneau ( brute )'))->setDescription(t(' Creneaux en affichage brute. '));

    $fields['status']->setDescription(t('A boolean indicating whether the Manage days entity is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);

    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')->setLabel(t('Revision translation affected'))->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))->setReadOnly(TRUE)->setRevisionable(TRUE)->setTranslatable(TRUE);

    return $fields;
  }

}
