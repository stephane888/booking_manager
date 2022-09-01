<?php

/**
 *
 * @file
 * Contains IcecreamManager.
 */
namespace Drupal\booking_manager;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Form\FormStateInterface;

/**
 * Icecream plugin manager.
 */
abstract class ManageDaysBase extends PluginBase implements ManageDaysInterface, ContainerFactoryPluginInterface {
  /**
   * The form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  protected $EntityFormBuilder;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a ReusableFormPluginBase object.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param FormBuilder $form_builder
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFormBuilder $EntityFormBuilder, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->EntityFormBuilder = $EntityFormBuilder;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity.form_builder'), $container->get('entity_type.manager'));
  }

  /**
   * Permet d'assoicier un type de prise de rdv à un type de contenu.
   *
   * {@inheritdoc}
   */
  public function buildForm(ConfigEntityBundleBase $entity) {
    // dump($this->pluginDefinition);
    return [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#value' => 'buildForm'
    ];
    // return $this->formBuilder->getForm($this->pluginDefinition['form'],
    // $entity);
  }

  /**
   * Permet de configurer une prise de rdv pour un contenu.
   * buildConfigForm(ContentEntityBase $entity)
   *
   * @return string[]
   */
  public function buildConfigForm(ContentEntityBase $entity) {
    $form = [];
    if (!$entity->isNew()) {
      $values = [
        'type' => $this->getTypeManageDays($entity)
      ];
      $entity = \Drupal::entityTypeManager()->getStorage($this->pluginDefinition['entity_id'])->create($values);
      $form = $this->EntityFormBuilder->getForm($entity, 'default');
      $form['#attributes']['class'][] = 'container';
    }
    else {
      \Drupal::messenger()->addMessage(' Le contenu doit exister ');
    }
    // On nettoie le formulaire pour pouvoir l'injecter dans celui du node.
    if (!empty($form['actions'])) {
      unset($form['actions']);
      unset($form['form_build_id']);
      unset($form['form_token']);
      unset($form['form_id']);
      unset($form['#submit']);
      // Also remove all other properties that start with a '#'.
      foreach ($form as $key => &$value) {
        if (strpos($key, '#') === 0) {
          unset($form[$key]);
        }
        elseif (isset($value['#tree'])) {
          $value['#tree'] = true;
          $this->addParentsInArray($value);
        }
      }
      // active tree of field;
    }
    return $form;
  }

  /**
   * On a essayer d'ajouter les elements au tableau #parents et #array_parents.
   * Mauvaise approcha, il faut se baser sur les subform.
   *
   * @param array $value
   */
  function addParentsInArray(array &$value) {
    foreach ($value as $k => &$val) {
      if ($k == '#parents' || $k == '#array_parents') {
        $temp = [
          'booking_manager'
        ];
        $val = array_merge($temp, $val);
      }
    }
  }

  /**
   * --
   */
  protected function getTypeManageDays(ContentEntityBase $entity) {
    $key = $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $entity->id();
    $key_id = preg_replace('/[^a-z0-9\-]/', "_", $key);
    $entG = \Drupal::entityTypeManager()->getStorage($this->pluginDefinition['entity_type_id']);
    $val = $entG->load($key_id);
    if (empty($val)) {
      $values = [
        'id' => $key_id,
        'label' => $key
      ];
      $entityType = \Drupal::entityTypeManager()->getStorage($this->pluginDefinition['entity_type_id'])->create($values);
      $entityType->save();
      return $key_id;
    }
    return $key_id;
  }

  /**
   *
   * @deprecated
   * @return array
   */
  public function buildConfigFormNone() {
    $entG = \Drupal::entityTypeManager()->getStorage($this->pluginDefinition['entity_type_id']);
    $val = $entG->load('testkksa888');
    if (empty($val)) {
      $entityType = \Drupal::entityTypeManager()->getStorage($this->pluginDefinition['entity_type_id'])->create([
        'id' => 'testkksa888'
      ]);
      $entityType->save();
      \Drupal::messenger()->addMessage('create testkksa888');
    }

    $values = [
      'type' => 'testkksa888'
    ];
    $entity = \Drupal::entityTypeManager()->getStorage($this->pluginDefinition['entity_id'])->create($values);
    $form = $this->EntityFormBuilder->getForm($entity, 'default');
    if (!empty($form['actions-'])) {
      // unset($form['actions']);
      unset($form['form_build_id']);
      unset($form['form_token']);
      unset($form['form_id']);
      //
      // Also remove all other properties that start with a '#'.
      foreach ($form as $key => $value) {
        if (strpos($key, '#') === 0) {
          unset($form[$key]);
        }
      }
      $form['actions']['submit']['#submit'][] = [
        $this,
        '_booking_manager_form_submit'
      ];
      $form['#validate'][] = [
        $this,
        '_booking_manager_form_submit'
      ];
      $form['#entity_builders'][] = [
        $this,
        '_booking_manager_form_submit'
      ];
      $form['actions']['#access'] = false;
    }
    return $form;
  }

  function _booking_manager_form_submit(&$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    dump($values);
    // die();
    // $form_state = new FormState();
    // $form_state->setValues($values);
  }

}