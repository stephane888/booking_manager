<?php

/**
 * @file
 * Contains manage_days_entity.page.inc.
 *
 * Page callback for Manage days entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Manage days entity templates.
 *
 * Default template: manage_days_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_manage_days_entity(array &$variables) {
  // Fetch ManageDaysEntity Entity Object.
  $manage_days_entity = $variables['elements']['#manage_days_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
