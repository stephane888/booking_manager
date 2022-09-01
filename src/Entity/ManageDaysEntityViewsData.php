<?php

namespace Drupal\booking_manager\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Manage days entity entities.
 */
class ManageDaysEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
