<?php

namespace Drupal\booking_manager\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "booking_manager_haraire_day",
 *   admin_label = @Translation("Horaire du jour"),
 *   category = @Translation("Booking Manager")
 * )
 */
class HoraireDayBlock extends BlockBase {

  /**
   *
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if (!empty($this->configuration['entity_id'])) {
      /**
       *
       * @var \Drupal\booking_manager\ManageDaysBase $manage_days
       */
      $manage_days = \Drupal::service('plugin.manager.booking_manager.manage_days')->createInstance($this->configuration['booking_manager_plugin']);
      $entity = \Drupal::entityTypeManager()->getStorage($this->configuration['entity_type_id'])->load($this->configuration['entity_id']);
      $datasRdv = [];
      if ($entity) {
        $datasRdv = $manage_days->getBaseConfig($entity);
        if (!empty($datasRdv)) {
          $datasRdv['today'] = $datasRdv['jours'][date('w')]['h_f'] . 'h';
          $datasRdv['today'] .= !empty($datasRdv['jours'][date('w')]['m_f']) ? $datasRdv['jours'][date('w')]['m_f'] : '00';
        }
      }
      $build['content'] = [
        '#theme' => 'booking_manager_horaire',
        '#content' => [
          'conf' => $this->configuration,
          'entity' => $datasRdv
        ]
      ];
    }
    return $build;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form_plugins = \Drupal::service('plugin.manager.booking_manager.manage_days')->getDefinitions();
    $options = [];

    foreach ($form_plugins as $name => $plugin) {
      $options[$name] = $plugin['title'];
    }
    $form['booking_manager_plugin'] = [
      '#type' => 'radios',
      '#title' => t('Available forms'),
      '#default_value' => $this->configuration['booking_manager_plugin'],
      '#options' => $options
    ];

    $form['entity_id'] = [
      '#type' => 'number',
      '#title' => t('Entity id'),
      '#default_value' => $this->configuration['entity_id']
    ];

    return $form;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    // save
    $this->configuration['booking_manager_plugin'] = $form_state->getValue('booking_manager_plugin');
    $this->configuration['entity_id'] = $form_state->getValue('entity_id');
  }

  /**
   *
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'booking_manager_plugin' => 'default_model',
      'entity_type_id' => 'node',
      'entity_id' => 0
    ];
  }

}
