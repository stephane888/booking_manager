<?php

namespace Drupal\booking_manager\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Manage days entity entities.
 *
 * @ingroup booking_manager
 */
interface ManageDaysEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Manage days entity name.
   *
   * @return string
   *   Name of the Manage days entity.
   */
  public function getName();

  /**
   * Sets the Manage days entity name.
   *
   * @param string $name
   *   The Manage days entity name.
   *
   * @return \Drupal\booking_manager\Entity\ManageDaysEntityInterface
   *   The called Manage days entity entity.
   */
  public function setName($name);

  /**
   * Gets the Manage days entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Manage days entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Manage days entity creation timestamp.
   *
   * @param int $timestamp
   *   The Manage days entity creation timestamp.
   *
   * @return \Drupal\booking_manager\Entity\ManageDaysEntityInterface
   *   The called Manage days entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Manage days entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Manage days entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\booking_manager\Entity\ManageDaysEntityInterface
   *   The called Manage days entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Manage days entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Manage days entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\booking_manager\Entity\ManageDaysEntityInterface
   *   The called Manage days entity entity.
   */
  public function setRevisionUserId($uid);

}
