<?php

namespace Drupal\booking_manager\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\booking_manager\Entity\ManageDaysEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ManageDaysEntityController.
 *
 *  Returns responses for Manage days entity routes.
 */
class ManageDaysEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Manage days entity revision.
   *
   * @param int $manage_days_entity_revision
   *   The Manage days entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($manage_days_entity_revision) {
    $manage_days_entity = $this->entityTypeManager()->getStorage('manage_days_entity')
      ->loadRevision($manage_days_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('manage_days_entity');

    return $view_builder->view($manage_days_entity);
  }

  /**
   * Page title callback for a Manage days entity revision.
   *
   * @param int $manage_days_entity_revision
   *   The Manage days entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($manage_days_entity_revision) {
    $manage_days_entity = $this->entityTypeManager()->getStorage('manage_days_entity')
      ->loadRevision($manage_days_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $manage_days_entity->label(),
      '%date' => $this->dateFormatter->format($manage_days_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Manage days entity.
   *
   * @param \Drupal\booking_manager\Entity\ManageDaysEntityInterface $manage_days_entity
   *   A Manage days entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ManageDaysEntityInterface $manage_days_entity) {
    $account = $this->currentUser();
    $manage_days_entity_storage = $this->entityTypeManager()->getStorage('manage_days_entity');

    $langcode = $manage_days_entity->language()->getId();
    $langname = $manage_days_entity->language()->getName();
    $languages = $manage_days_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $manage_days_entity->label()]) : $this->t('Revisions for %title', ['%title' => $manage_days_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all manage days entity revisions") || $account->hasPermission('administer manage days entity entities')));
    $delete_permission = (($account->hasPermission("delete all manage days entity revisions") || $account->hasPermission('administer manage days entity entities')));

    $rows = [];

    $vids = $manage_days_entity_storage->revisionIds($manage_days_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\booking_manager\Entity\ManageDaysEntityInterface $revision */
      $revision = $manage_days_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $manage_days_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.manage_days_entity.revision', [
            'manage_days_entity' => $manage_days_entity->id(),
            'manage_days_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $manage_days_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.manage_days_entity.translation_revert', [
                'manage_days_entity' => $manage_days_entity->id(),
                'manage_days_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.manage_days_entity.revision_revert', [
                'manage_days_entity' => $manage_days_entity->id(),
                'manage_days_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.manage_days_entity.revision_delete', [
                'manage_days_entity' => $manage_days_entity->id(),
                'manage_days_entity_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['manage_days_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
