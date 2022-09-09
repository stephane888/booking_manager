<?php

namespace Drupal\booking_manager;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Manage days entity entities.
 *
 * @ingroup booking_manager
 */
class ManageDaysEntityListBuilder extends EntityListBuilder {

  /**
   *
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Manage days entity ID');
    $header['name'] = $this->t('Name');
    $header['creneau'] = $this->t('Creneau');
    return $header + parent::buildHeader();
  }

  /**
   *
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\booking_manager\Entity\ManageDaysEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute($entity->label(), 'entity.manage_days_entity.edit_form', [
      'manage_days_entity' => $entity->id()
    ]);
    $date = $entity->getCreneau();
    $row['creneau'] = $date['value'] . ' || ' . $date['end_value'];
    return $row + parent::buildRow($entity);
  }

}
