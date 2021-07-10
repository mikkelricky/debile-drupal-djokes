<?php

namespace Drupal\debile_djokes\Plugin\views\field;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * View access per node field.
 *
 * @ViewsField("vapn")
 */
class VapnField extends FieldPluginBase implements PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The role storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|mixed|object
   */
  private $roleStorage;

  /**
   * All roles indexed by role id.
   *
   * @var array
   */
  private $roles;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Connection $database,
    EntityTypeManager $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->roleStorage = $entityTypeManager->getStorage('user_role');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  public function clickSortable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);
    if ($entity instanceof NodeInterface) {
      // Get the records for this node.
      $rolesIds = $this->database
        ->select('vapn')
        ->fields('vapn', ['rid'])
        ->condition('nid', $entity->id())
        ->execute()
        ->fetchCol();

      if (NULL === $this->roles) {
        $this->roles = $this->roleStorage->loadMultiple();
      }

      return implode(', ', array_filter(array_map(function (string $roleId) {
        return isset($this->roles[$roleId]) ? $this->roles[$roleId]->label() : NULL;
      }, $rolesIds)));
    }

    return '';
  }

}
