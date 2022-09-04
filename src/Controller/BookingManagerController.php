<?php

namespace Drupal\booking_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stephane888\Debug\Utility;

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
        /**
         *
         * @var \Drupal\booking_manager\ManageDaysBase $manage_days
         */
        $manage_days = \Drupal::service('plugin.manager.booking_manager.manage_days')->createInstance($ThirdPartySettings['plugin']);
        return $manage_days->buildConfigForm($content);
      }
    }
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('nothing')
    ];
    return $build;
  }

  /**
   *
   * @param string $entity_type_id
   * @param int $entity_id
   */
  public function datasRdv(string $entity_type_id, $entity_id) {
    $content = $this->entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
    try {
      if ($content) {
        $nodeType = \Drupal\node\Entity\NodeType::load($content->getType());
        $ThirdPartySettings = $nodeType->getThirdPartySettings('booking_manager');
        if (!empty($ThirdPartySettings['enabled']) && !empty($ThirdPartySettings['plugin'])) {
          /**
           *
           * @var \Drupal\booking_manager\ManageDaysBase $manage_days
           */
          $manage_days = \Drupal::service('plugin.manager.booking_manager.manage_days')->createInstance($ThirdPartySettings['plugin']);
          return $this->reponse($manage_days->getDatasRdv($content));
        }
      }
      throw new \Exception('Le type ne suppoerte pas la prise de RDV.');
    }
    catch (\Exception $e) {
      return $this->reponse(Utility::errorAll($e), '400', $e->getMessage());
    }
  }

  /**
   *
   * @param Array|string $configs
   * @param number $code
   * @param string $message
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function reponse($configs, $code = null, $message = null) {
    if (!is_string($configs))
      $configs = Json::encode($configs);
    $reponse = new JsonResponse();
    if ($code)
      $reponse->setStatusCode($code, $message);
    $reponse->setContent($configs);
    return $reponse;
  }

}
