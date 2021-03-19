<?php

namespace Drupal\debile_djokes_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;

/**
 * Collection fixture.
 *
 * @package Drupal\debile_djokes_fixtures\Fixture
 */
class CollectionFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $collection = Node::create([
      'type' => 'collection',
      'title' => 'The first collection',
      'status' => Node::PUBLISHED,
    ]);
    $this->setReference('collection:the-first-collection', $collection);
    $collection->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['debile_djokes'];
  }

}
