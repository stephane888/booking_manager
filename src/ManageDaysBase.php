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
use Drupal\booking_manager\Entity\ManageDaysEntityType;

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
  protected $maxCreneau = 50;

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

  public function getDatasRdv(ContentEntityBase $entity) {
    if (!$entity->isNew()) {
      $confs = $this->getBaseConfig($entity);
      $nberDays = $confs['number_week'] * 7;
      $runDateDay = new \DateTime('Now');
      $dateToday = new \DateTime('Now');
      $result['jours'] = [];
      for ($i = 0; $i < $nberDays; $i++) {
        $dayConf = $confs['jours'][$runDateDay->format('w')];
        $result['jours'][] = [
          'label' => $runDateDay->format("D.") . '<br>' . $runDateDay->format("j M"),
          'value' => $runDateDay->format("D j M Y"),
          'date' => $runDateDay->format("Y-m-d H:i:s"),
          'conf' => $dayConf,
          'creneau' => $dayConf['status'] ? $this->buildCreneauOfDay($runDateDay, $dateToday, $confs, $dayConf) : []
        ];
        $runDateDay->modify('+1 day');
      }
      $result['entityType'] = $confs;
      return $result;
    }
    throw new \Exception("Le contenu n'est pas definit");
  }

  public function getBaseConfig(ContentEntityBase $entity) {
    $key = $this->getTypeId($entity);
    $entityType = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id'])->load($key);
    if (!empty($entityType))
      return $entityType->toArray();
    throw new \Exception("Aucune configuration disponible");
  }

  protected function buildCreneauOfDay(\DateTime $day, $dateToday, array $entityArray, array $dayConf) {
    $creneaux = [];
    $day_string = $day->format("Y-m-d H:i:s");
    $d = new \DateTime($day_string);
    $f = new \DateTime($day_string);
    $d->setTime($dayConf['h_d'], $dayConf['m_d']);
    $f->setTime($dayConf['h_f'], $dayConf['m_f']);
    $interval = !empty($entityArray['interval']) ? $entityArray['interval'] : 30;

    if ($f > $d) {
      $i = 0;
      while ($f > $d && $i < $this->maxCreneau) {
        $i++;
        $creneaux[] = [
          'value' => $d->format('H:i'),
          'status' => ($dateToday > $d) ? false : true
        ];
        $d->modify("+ " . $interval . " minutes");
      }
    }
    return $creneaux;
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
      $entityType = $this->getEntityDay($entity);
      if ($entityType) {
        $entityType->set('label', $this->getTypeLabel($entity));
        $form = $this->EntityFormBuilder->getForm($entityType, 'edit');
      }
      else {
        $key = $this->getTypeId($entity);
        $values = [
          'id' => $key,
          'label' => $this->getTypeLabel($entity)
        ];
        $entity = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id'])->create($values);
        $form = $this->EntityFormBuilder->getForm($entity, 'add');
      }
    }
    else {
      \Drupal::messenger()->addMessage(' Le contenu doit exister ');
    }
    $form['#attributes']['class'][] = 'container';
    return $form;
  }

  /**
   * Permet de recuperer la configuration d'un rdv à partir d'une entitée.
   *
   * @param ContentEntityBase $entity
   * @return ManageDaysEntityType
   */
  public function getEntityDay(ContentEntityBase $entity) {
    $key = $this->getTypeId($entity);
    $storage = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id']);
    return $storage->load($key);
  }

  /**
   * Permet de cloner et enregistrer la configuration d'une entité ( $entity )
   * vers une autre
   * ($entityClone).
   *
   * @param ContentEntityBase $entity
   * @param ContentEntityBase $entityClone
   * @return ManageDaysEntityType|null
   */
  public function CloneFromAnother(ContentEntityBase $entity, ContentEntityBase $entityClone) {
    $entityType = $this->getEntityDay($entity);
    //
    if ($entityType) {
      $entityTypeClone = $this->getEntityDay($entityClone);
      if (empty($entityTypeClone)) {
        $entityType = $entityType->createDuplicate()->toArray();
        $entityType['id'] = $this->getTypeId($entityClone);
        $entityType['label'] = $this->getTypeLabel($entityClone);
        $entityTypeClone = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id'])->create($entityType);
        $entityTypeClone->save();
      }
      return $entityTypeClone;
    }
    return;
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

  public function saveTypeByArray(array $values) {
    if (!empty($values['id']) && !empty($values['label'])) {
      $entG = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id']);
      /**
       *
       * @var \Drupal\booking_manager\Entity\ManageDaysEntityType $type
       */
      $type = $entG->load($values['id']);
      if (!empty($type)) {
        if (!empty($values['jours']))
          $type->set('jours', $values['jours']);

        $type->save();
        return $type->id();
      }
      else {
        $entityType = $entG->create($values);
        $entityType->save();
        return $entityType->id();
      }
    }
    else
      return false;
  }

  /**
   *
   * @param ContentEntityBase $entity
   * @return mixed
   */
  protected function getTypeId(ContentEntityBase $entity) {
    return preg_replace('/[^a-z0-9\-]/', "_", $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $entity->id());
  }

  /**
   *
   * @param ContentEntityBase $entity
   * @return mixed
   */
  protected function getTypeLabel(ContentEntityBase $entity) {
    return $entity->getTitle() . ' (' . $entity->getEntityTypeId() . '.' . $entity->bundle() . ')';
  }

  /**
   * --
   */
  protected function createTypeManageDays(ContentEntityBase $entity, $key) {
    $entG = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id']);
    $val = $entG->load($key);
    if (empty($val)) {
      $values = [
        'id' => $key,
        'label' => $entity
      ];
      $entityType = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id'])->create($values);
      $entityType->save();
      return $key;
    }
    return $key;
  }

  /**
   *
   * @deprecated
   * @return array
   */
  public function buildConfigFormNone() {
    $entG = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id']);
    $val = $entG->load('testkksa888');
    if (empty($val)) {
      $entityType = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_type_id'])->create([
        'id' => 'testkksa888'
      ]);
      $entityType->save();
      \Drupal::messenger()->addMessage('create testkksa888');
    }

    $values = [
      'type' => 'testkksa888'
    ];
    $entity = $this->entityTypeManager->getStorage($this->pluginDefinition['entity_id'])->create($values);
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