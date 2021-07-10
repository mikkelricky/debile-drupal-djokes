<?php

namespace Drupal\debile_djokes\Plugin\views\filter;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\user\Entity\Role;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * View access per node filter.
 *
 * @ViewsFilter("vapn")
 */
class VapnFilter extends ManyToOne {

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
    Connection $database,
    EntityTypeManager $entityTypeManager,
    array $configuration,
    $plugin_id,
    $plugin_definition
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
      $container->get('database'),
      $container->get('entity_type.manager'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(
    ViewExecutable $view,
    DisplayPluginBase $display,
    array &$options = NULL
  ) {
    parent::init($view, $display, $options);
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * {@inheritdoc}
   */
  public function generateOptions() {
    $roles = $this->roleStorage->loadMultiple();

    return array_filter(array_map(static function (Role $role) {
      return $role->label();
    }, $roles));
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if (!empty($this->value) && $this->query instanceof Sql) {
      $configuration = [
        'left_table' => 'node_field_data',
        'left_field' => 'nid',
        'table' => 'vapn',
        'field' => 'nid',
        'operator' => '=',
      ];

      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addRelationship('vapn', $join, 'vapn');
      $this->query->addWhere('AND', 'vapn.rid', $this->value);
    }
  }

}
