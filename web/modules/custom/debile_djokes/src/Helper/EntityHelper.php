<?php

namespace Drupal\debile_djokes\Helper;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;

/**
 * Entity hook implementations.
 */
class EntityHelper {

  /**
   * Implements hook_entity_presave().
   *
   * Set djoke title based en djoke text.
   */
  public function presave(EntityInterface $entity) {
    if ($entity instanceof NodeInterface) {
      if ('djoke' === $entity->bundle()) {
        $title = Unicode::truncate($entity->get('djoke')->getString(), 64, TRUE,
          TRUE);
        $entity->setTitle($title);
      }
    }
  }

}
