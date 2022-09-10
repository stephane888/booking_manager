<?php

namespace Drupal\booking_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stephane888\Debug\Utility;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Booking Manager routes.
 */
class BookingManagerController extends ControllerBase {

  /**
   *
   * @return string[]|\Drupal\Core\StringTranslation\TranslatableMarkup[]
   */
  public function souscriptionRdv(Request $request, string $entity_type_id, $entity_id) {
    $build['content'] = [
      '#type' => 'html_tag',
      '#tag' => 'section',
      "#attributes" => [
        'id' => 'app-prise-rdv',
        'class' => [
          'm-5',
          'p-5'
        ]
      ]
    ];
    $build['content']['#attached']['library'][] = 'booking_manager/prise_rdv';
    // $build['content']['#attached']['drupalSettings']['vuejs_entity']['language']
    // = \Drupal::languageManager()->getCurrentLanguage();
    // $request->query->add([
    // 'node' => $entity_type_id
    // ]);

    return $build;
  }

  /**
   *
   * @return string[]|\Drupal\Core\StringTranslation\TranslatableMarkup[]
   */
  public function SaveSouscriptionRdv(Request $request, string $entity_type_id, $entity_id) {
    try {
      $datas = Json::decode($request->getContent());
      $content = $this->entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
      if ($content && $datas) {
        $BundleEntityType = $content->getEntityType()->getBundleEntityType();
        $ThirdPartySettings = $this->entityTypeManager()->getStorage($BundleEntityType)->load($content->bundle())->getThirdPartySettings('booking_manager');
        if (!empty($ThirdPartySettings['enabled']) && !empty($ThirdPartySettings['plugin'])) {
          $day = new \DateTime($datas['date']);
          // $d->setDate($year, $month, $day);
          /**
           *
           * @var \Drupal\booking_manager\ManageDaysBase $manage_days
           */
          $manage_days = \Drupal::service('plugin.manager.booking_manager.manage_days')->createInstance($ThirdPartySettings['plugin']);
          $BaseConfig = $manage_days->getBaseConfig($content);
          $time = explode(":", $datas['creneaux']);
          if (!empty($time[0])) {
            $values = [
              'name' => $content->label(),
              'creneau' => [
                'value' => $day->setTime($time[0], $time[1])->format("Y-m-d\TH-i-s"),
                'end_value' => $day->modify("+ " . $BaseConfig["interval"] . " minutes")->format("Y-m-d\TH-i-s")
              ],
              'creneau_string' => $datas['creneaux']
            ];
            $datas['values'] = $values;
            $datas['save'] = $manage_days->SaveRdv($content, $values);
          }
        }
      }
      return $this->reponse($datas);
    }
    catch (\Exception $e) {
      return $this->reponse(Utility::errorAll($e), 400, $e->getMessage());
    }
  }

  /**
   * Permet de configurer un rdv à partir de l'url.
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
   * Retourne les données JSON.
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
          return $this->reponse([
            'data_creneaux' => $manage_days->getDatasRdv($content),
            'data_to_rdv' => $this->getDataToRdv($entity_type_id, $entity_id)
          ]);
        }
      }
      throw new \Exception('Le type ne suppoerte pas la prise de RDV.');
    }
    catch (\Exception $e) {
      return $this->reponse(Utility::errorAll($e), '400', $e->getMessage());
    }
  }

  protected function getDataToRdv(string $entity_type_id, $entity_id) {
    $datas = $this->entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
    if ($datas) {
      return $datas->toArray();
    }
    return [];
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
