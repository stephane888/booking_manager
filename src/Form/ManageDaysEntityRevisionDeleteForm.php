<?php

namespace Drupal\booking_manager\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Manage days entity revision.
 *
 * @ingroup booking_manager
 */
class ManageDaysEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Manage days entity revision.
   *
   * @var \Drupal\booking_manager\Entity\ManageDaysEntityInterface
   */
  protected $revision;

  /**
   * The Manage days entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $manageDaysEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->manageDaysEntityStorage = $container->get('entity_type.manager')->getStorage('manage_days_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_days_entity_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.manage_days_entity.version_history', ['manage_days_entity' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $manage_days_entity_revision = NULL) {
    $this->revision = $this->ManageDaysEntityStorage->loadRevision($manage_days_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->ManageDaysEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Manage days entity: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Manage days entity %title has been deleted.', ['%revision-date' => \Drupal::service('date.formatter')->format($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.manage_days_entity.canonical',
       ['manage_days_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {manage_days_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.manage_days_entity.version_history',
         ['manage_days_entity' => $this->revision->id()]
      );
    }
  }

}
