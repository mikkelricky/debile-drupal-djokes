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
      'collection' => [['target_id' => $this->getReference('collection:500-debile-djokes')->id()]],
      'index' => 1,
      'djoke' => 'Hvad kan man kalde et musikinstrument, som kun kan spilles på kl 12.00?',
      'punchline' => 'En middagslur',
      'status' => Node::PUBLISHED,
    ]);
    $djoke->save();

    $djoke = Node::create([
      'type' => 'djoke',
      'collection' => [['target_id' => $this->getReference('collection:500-debile-djokes')->id()]],
      'index' => 2,
      'djoke' => 'Hvad kan man kalde et køretøj med hjerne?',
      'punchline' => 'En tank-bil',
      'status' => Node::PUBLISHED,
    ]);
    $djoke->save();

    $djoke = Node::create([
      'type' => 'djoke',
      'collection' => [['target_id' => $this->getReference('collection:500-debile-djokes')->id()]],
      'index' => 3,
      'djoke' => 'Djoke med licens (500)',
      'punchline' => 'Hep!',
      'status' => Node::PUBLISHED,
      'license' => [
        $this->getReference('license:500-debile-djokes')->id(),
      ],
    ]);
    $djoke->save();

    $djoke = Node::create([
      'type' => 'djoke',
      'collection' => [['target_id' => $this->getReference('collection:500-nye-debile-djokes')->id()]],
      'index' => 3,
      'djoke' => 'Djoke med licens (500 nye)',
      'punchline' => 'Hep!',
      'status' => Node::PUBLISHED,
      'license' => [
        $this->getReference('license:500-nye-debile-djokes')->id(),
      ],
    ]);
    $djoke->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      CollectionFixture::class,
      LicenseFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['debile-djokes', 'djoke'];
  }

}
