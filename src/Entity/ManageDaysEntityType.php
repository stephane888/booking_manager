<?php

namespace Drupal\booking_manager\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;

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
 *     "jours",
 *     "disabled_dates",
 *     "disabled_periode",
 *     "interval",
 *     "decallage",
 *     "number_week"
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
  protected $jours = [];
  protected $disabled_periode = [];
  protected $disabled_dates = [];
  protected $interval = 60;
  protected $decallage = 0;
  protected $number_week = 6;

  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $jours = $this->get('jours');
    foreach ($jours as $k => $val) {
      $d = explode(":", $val['h_d__m_d']);
      $jours[$k]['h_d'] = $d[0];
      $jours[$k]['m_d'] = isset($d[1]) ? $d[1] : 0;
      unset($jours[$k]['h_d__m_d']);
      //
      $f = explode(":", $val['h_f__m_f']);
      $jours[$k]['h_f'] = $f[0];
      $jours[$k]['m_f'] = isset($f[1]) ? $f[1] : 0;
      unset($jours[$k]['h_f__m_f']);
    }
    $this->set('jours', $jours);
  }

}
