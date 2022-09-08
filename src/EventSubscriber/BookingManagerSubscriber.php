<?php

namespace Drupal\booking_manager\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\vuejs_entity\Event\DuplicateEntityEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\booking_manager\ManageDaysPluginManger;

/**
 * Booking Manager event subscriber.
 */
class BookingManagerSubscriber implements EventSubscriberInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  protected $EntityTypeManager;
  /**
   *
   * @var ManageDaysPluginManger
   */
  protected $ManageDaysPluginManger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *        The messenger.
   */
  public function __construct(MessengerInterface $messenger, EntityTypeManagerInterface $EntityTypeManager, ManageDaysPluginManger $ManageDaysPluginManger) {
    $this->messenger = $messenger;
    $this->EntityTypeManager = $EntityTypeManager;
    $this->ManageDaysPluginManger = $ManageDaysPluginManger;
  }

  /**
   * Kernel request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *        Response event.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    // $this->messenger->addStatus(__FUNCTION__);
  }

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *        Response event.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    // $this->messenger->addStatus(__FUNCTION__);
  }

  /**
   * Permet de savoir qu'un contenu a été cloné.
   *
   * @param DuplicateEntityEvent $DuplicateEntityEvent
   */
  public function DuplicateEntity(DuplicateEntityEvent $DuplicateEntityEvent) {
    /**
     *
     * @var \Drupal\node\Entity\Node $entityClone
     */
    $entityClone = $DuplicateEntityEvent->entityClone;

    /**
     *
     * @var \Drupal\node\Entity\Node $entityClone
     */
    $entity = $DuplicateEntityEvent->entity;

    // if($entityClone->getEn)
    if ($entityClone->getEntityType() instanceof \Drupal\Core\Entity\ContentEntityType) {
      if ($entityClone->getEntityType()->hasKey('bundle')) {
        $entityTypeId = $entityClone->getEntityType()->getBundleEntityType();
        $entityType = $this->EntityTypeManager->getStorage($entityTypeId)->load($entityClone->bundle());
        $ThirdPartySettings = $entityType->getThirdPartySettings('booking_manager');
        //
        if (!empty($ThirdPartySettings['enabled']) && !empty($ThirdPartySettings['plugin'])) {
          try {
            /**
             *
             * @var \Drupal\booking_manager\ManageDaysBase $manage_days
             */
            $manage_days = $this->ManageDaysPluginManger->createInstance($ThirdPartySettings['plugin']);
            $manage_days->CloneFromAnother($entity, $entityClone);
          }
          catch (\Exception $e) {
            \Drupal::logger('booking_manager')->alert($e->getMessage());
          }
        }
      }
    }
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      DuplicateEntityEvent::EVENT_NAME => [
        'DuplicateEntity'
      ],
      KernelEvents::REQUEST => [
        'onKernelRequest'
      ],
      KernelEvents::RESPONSE => [
        'onKernelResponse'
      ]
    ];
  }

}
