<?php

namespace Drupal\debile_djokes_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * License fixture.
 *
 * @package Drupal\debile_djokes_fixtures\Fixture
 */
class LicenseFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $license = Term::create([
      'vid' => 'license',
      'name' => '500 Debile Djokes',
    ]);
    $this->setReference('license:500-debile-djokes', $license);
    $license->save();

    $license = Term::create([
      'vid' => 'license',
      'name' => '500 nye Debile Djokes',
    ]);
    $this->setReference('license:500-nye-debile-djokes', $license);
    $license->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['debile-djokes', 'license'];
  }

}
