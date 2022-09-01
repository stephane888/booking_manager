<?php

namespace Drupal\booking_manager\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ManageDaysEntityTypeForm.
 */
class ManageDaysEntityTypeForm extends EntityForm {

  /**
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
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $manage_days_entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\booking_manager\Entity\ManageDaysEntityType::load',
      ],
      '#disabled' => !$manage_days_entity_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $manage_days_entity_type = $this->entity;
    $status = $manage_days_entity_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Manage days entity type.', [
          '%label' => $manage_days_entity_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Manage days entity type.', [
          '%label' => $manage_days_entity_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($manage_days_entity_type->toUrl('collection'));
  }

}
