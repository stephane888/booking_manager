<?php
declare(strict_types = 1);

/**
 *
 * @file
 * Provides Drupal\icecream\FlavorInterface
 */
namespace Drupal\booking_manager\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\views\Plugin\views\field\Boolean;
use Drupal\booking_manager\Entity\ManageDaysEntityType;

/**
 * Defines a data fetcher annotation object.
 *
 * Plugin namespace: Plugin\migrate_plus\data_fetcher.
 *
 * @see \Drupal\migrate_plus\DataFetcherPluginBase
 * @see \Drupal\migrate_plus\DataFetcherPluginInterface
 * @see \Drupal\migrate_plus\DataFetcherPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class ManageDays extends Plugin {

  /**
   * The plugin ID.
   */
  public string $id;

  /**
   * The title of the plugin.
   */
  public string $title;

  /**
   * The list of the week
   */
  public array $days;

  /**
   * The entity : l'entité chargé de la sauvegarde
   */
  public $entity_id;
  public $entity_type_id;

  /**
   * Masque les jours désactivées.
   *
   * @var boolean
   */
  public $hidden_date_disable;

}