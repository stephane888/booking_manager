<?php

namespace Drupal\booking_manager\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ManageDaysEntityTypeForm.
 */
class ManageDaysEntityTypeForm extends EntityForm {

  /**
   *
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $manage_days_entity_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $manage_days_entity_type->label(),
      '#description' => $this->t("Label for the Manage days entity type."),
      '#required' => TRUE,
      '#attributes' => [
        'disabled' => true
      ],
      '#weight' => 100
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $manage_days_entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\booking_manager\Entity\ManageDaysEntityType::load'
      ],
      '#disabled' => !$manage_days_entity_type->isNew()
    ];

    /* You will need additional form elements for your custom properties. */
    $jours = \Drupal\booking_manager\ManageDaysInterface::jours;
    if (!empty($manage_days_entity_type->get('jours'))) {
      $jours = $manage_days_entity_type->get('jours');
    }
    $form['jours'] = [
      '#type' => 'fieldset',
      '#title' => 'Configuration des dates',
      '#tree' => TRUE
    ];
    //
    foreach ($jours as $i => $val) {
      //
      $form['jours'][$i] = [
        "#type" => 'details',
        '#title' => $val['label'],
        '#open' => false
      ];
      $form['jours'][$i]['label'] = [
        "#type" => 'textfield',
        '#title' => 'Label',
        '#default_value' => $val['label']
      ];
      //
      $form['jours'][$i]['status'] = [
        "#type" => 'checkbox',
        '#title' => 'Status',
        '#default_value' => $val['status']
      ];
      //
      $form['jours'][$i]['h_d__m_d'] = [
        "#type" => 'textfield',
        '#title' => 'Heure debut',
        '#default_value' => $val['h_d'] . ':' . $val['m_d']
      ];
      //
      $form['jours'][$i]['h_f__m_f'] = [
        "#type" => 'textfield',
        '#title' => 'Heure fin',
        '#default_value' => $val['h_f'] . ':' . $val['m_f']
      ];
    }
    $form['interval'] = [
      '#type' => 'number',
      '#title' => "Durée d'un creneau en minutes",
      '#default_value' => $manage_days_entity_type->get('interval')
    ];
    $form['decallage'] = [
      '#type' => 'number',
      '#title' => "Decallage entre deux creneau",
      '#default_value' => $manage_days_entity_type->get('decallage')
    ];
    $form['limit_reservation'] = [
      '#type' => 'number',
      '#title' => "Nombre de reservation par creneaux ",
      '#default_value' => $manage_days_entity_type->get('limit_reservation')
    ];
    return $form;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $manage_days_entity_type = $this->entity;
    $status = $manage_days_entity_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Manage days entity type.', [
          '%label' => $manage_days_entity_type->label()
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Manage days entity type.', [
          '%label' => $manage_days_entity_type->label()
        ]));
    }
    $form_state->setRedirectUrl($manage_days_entity_type->toUrl('collection'));
    // pour des utilisateurs avec des droits non admin, on doit rediriger sur la
    // liste via une vue.
  }

}
