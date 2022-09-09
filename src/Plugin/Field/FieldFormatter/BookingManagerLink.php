<?php

namespace Drupal\booking_manager\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\menu_item_extras\Entity\MenuItemExtrasMenuLinkContent;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\system\Entity\Menu;
use Drupal\system\Plugin\Block\SystemMenuBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "_booking_manager_link",
 *   label = @Translation("Affiche le lien de reservation"),
 *   description = @Translation(" Display the label of the referenced entities. "),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class BookingManagerLink extends FormatterBase {

  /**
   *
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [] + parent::defaultSettings();
  }

  /**
   *
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    //
    foreach ($items as $delta => $item) {
      /**
       *
       * @var \Drupal\node\Entity\Node $node
       */
      $node = $item->getEntity();
      // dump();
      $elements[] = [
        '#type' => 'link',
        '#title' => 'Choisir',
        '#options' => [
          'attributes' => [
            'class' => [
              'sd-btn',
              'sd-btn--small',
              'sd-btn--primary'
            ]
          ]
        ],
        '#url' => Url::fromRoute('booking_manager.rdv.souscription', [
          'entity_type_id' => $node->getEntityTypeId(),
          'entity_id' => $node->id()
        ])
      ];
    }
    //
    return $elements;
  }

}
