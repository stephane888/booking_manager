<?php

namespace Drupal\booking_manager\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Manage days entity type entity.
 *
 * @ConfigEntityType(
 *   id = "manage_days_entity_type",
 *   label = @Translation("Manage days entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\booking_manager\ManageDaysEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\booking_manager\Form\ManageDaysEntityTypeForm",
 *       "edit" = "Drupal\booking_manager\Form\ManageDaysEntityTypeForm",
 *       "delete" = "Drupal\booking_manager\Form\ManageDaysEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\booking_manager\ManageDaysEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "manage_days_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "manage_days_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/manage_days_entity_type/{manage_days_entity_type}",
 *     "add-form" = "/admin/structure/manage_days_entity_type/add",
 *     "edit-form" = "/admin/structure/manage_days_entity_type/{manage_days_entity_type}/edit",
 *     "delete-form" = "/admin/structure/manage_days_entity_type/{manage_days_entity_type}/delete",
 *     "collection" = "/admin/structure/manage_days_entity_type"
 *   }
 * )
 */
class ManageDaysEntityType extends ConfigEntityBundleBase implements ManageDaysEntityTypeInterface {

  /**
   * The Manage days entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Manage days entity type label.
   *
   * @var string
   */
  protected $label;

}
