<?php

namespace Drupal\booking_manager\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Booking Manager routes.
 */
class BookingManagerController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function manager($entity_type_id, $id) {
    $content = $this->entityTypeManager()->getStorage($entity_type_id)->load($id);
    if ($content) {
      $nodeType = \Drupal\node\Entity\NodeType::load($content->getType());
      $ThirdPartySettings = $nodeType->getThirdPartySettings('booking_manager');
      if (!empty($ThirdPartySettings['enabled']) && !empty($ThirdPartySettings['plugin'])) {
        $plugin_manager = \Drupal::service('plugin.manager.booking_manager.manage_days');
        return $plugin_manager->createInstance($ThirdPartySettings['plugin'])->buildConfigForm($content);
      }
    }
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('nothing')
    ];
    return $build;
  }

}
