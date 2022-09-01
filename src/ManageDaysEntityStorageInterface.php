<?php

namespace Drupal\booking_manager;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\booking_manager\Entity\ManageDaysEntityInterface;

/**
 * Defines the storage handler class for Manage days entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Manage days entity entities.
 *
 * @ingroup booking_manager
 */
interface ManageDaysEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Manage days entity revision IDs for a specific Manage days entity.
   *
   * @param \Drupal\booking_manager\Entity\ManageDaysEntityInterface $entity
   *   The Manage days entity entity.
   *
   * @return int[]
   *   Manage days entity revision IDs (in ascending order).
   */
  public function revisionIds(ManageDaysEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Manage days entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Manage days entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\booking_manager\Entity\ManageDaysEntityInterface $entity
   *   The Manage days entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ManageDaysEntityInterface $entity);

  /**
   * Unsets the language for all Manage days entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
