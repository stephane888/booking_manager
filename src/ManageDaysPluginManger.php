<?php

/**
 *
 * @file
 * Contains IcecreamManager.
 */
namespace Drupal\booking_manager;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\booking_manager\Annotation\ManageDays;

/**
 * Icecream plugin manager.
 */
class ManageDaysPluginManger extends DefaultPluginManager {
  
  /**
   * Constructs an ManageDaysPluginManger object.
   *
   * @param \Traversable $namespaces
   *        An object that implements \Traversable which contains the root paths
   *        keyed by the corresponding namespace to look for plugin
   *        implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *        Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *        The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/BookingManager/ManageDays', $namespaces, $module_handler, ManageDaysInterface::class, ManageDays::class);
    //
    $this->alterInfo('manage_days_info');
    $this->setCacheBackend($cache_backend, 'booking_manager_manage_days');
  }
  
}