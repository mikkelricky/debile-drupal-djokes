<?php

namespace Drupal\debile_djokes\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Views hook implementations.
 */
class ViewsHelper {
  use StringTranslationTrait;

  /**
   * Implements hook_views_data().
   *
   * Add View access per node field.
   */
  public function data() {
    $data['views']['table']['group'] = $this->t('View access per node');
    $data['views']['table']['join'] = [
      // #global is a special flag which allows a table to appear all the time.
      '#global' => [],
    ];

    $data['views']['vapn'] = [
      'title' => t('View access per node'),
      'help' => t('View access per node.'),
      'field' => [
        'id' => 'vapn',
      ],
      'filter' => [
        'field' => 'nid',
        'id' => 'vapn',
      ],

    ];

    return $data;
  }

}
