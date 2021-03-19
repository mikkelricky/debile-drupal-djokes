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
      'title' => '500 Debile Djokes',
      'status' => Node::PUBLISHED,
    ]);
    $this->setReference('collection:500-debile-djokes', $collection);
    $collection->save();

    $collection = Node::create([
      'type' => 'collection',
      'title' => '500 nye Debile Djokes',
      'status' => Node::PUBLISHED,
    ]);
    $this->setReference('collection:500-nye-debile-djokes', $collection);
    $collection->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['debile_djokes'];
  }

}
