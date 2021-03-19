<?php

namespace Drupal\debile_djokes_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;

/**
 * Djoke fixture.
 *
 * @package Drupal\debile_djokes_fixtures\Fixture
 */
class DjokeFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $djoke = Node::create([
      'type' => 'djoke',
      'collection' => [['target_id' => $this->getReference('collection:the-first-collection')->id()]],
      'index' => 1,
      'djoke' => 'The first djoke',
      'punchline' => 'The first punchline',
      'status' => Node::PUBLISHED,
    ]);
    $djoke->save();

    $djoke = Node::create([
      'type' => 'djoke',
      'collection' => [['target_id' => $this->getReference('collection:the-first-collection')->id()]],
      'index' => 2,
      'djoke' => 'Another djoke',
      'punchline' => 'The second punchline',
      'status' => Node::PUBLISHED,
    ]);
    $djoke->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      CollectionFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['debile_djokes'];
  }

}
