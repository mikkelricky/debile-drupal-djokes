<?php

namespace Drupal\debile_djokes_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;

/**
 * User fixture.
 *
 * @package Drupal\debile_djokes_fixtures\Fixture
 */
class UserFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * The user data.
   *
   * @var \Drupal\user\UserDataInterface
   */
  private $userData;

  /**
   * Constructor.
   */
  public function __construct(UserDataInterface $userData) {
    $this->userData = $userData;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $user = User::create([
      'name' => 'administrator@debiledjokes.dk',
      'mail' => 'administrator@debiledjokes.dk',
      'status' => 1,
      'roles' => [
        'administrator',
      ],
    ]);
    $user->save();

    $user = User::create([
      'name' => 'editor@debiledjokes.dk',
      'mail' => 'editor@debiledjokes.dk',
      'status' => 1,
      'roles' => [
        'editor',
      ],
    ]);
    $user->save();

    $user = User::create([
      'name' => 'user@debiledjokes.dk',
      'mail' => 'user@debiledjokes.dk',
      'status' => 1,
    ]);
    $user->save();

    $user = User::create([
      'name' => '500@debiledjokes.dk',
      'mail' => '500@debiledjokes.dk',
      'status' => 1,
    ]);
    $user->save();
    $this->setLicenses($user, [$this->getReference('license:500-debile-djokes')]);

    $user = User::create([
      'name' => '500-nye@debiledjokes.dk',
      'mail' => '500-nye@debiledjokes.dk',
      'status' => 1,
    ]);
    $user->save();
    $this->setLicenses($user, [$this->getReference('license:500-nye-debile-djokes')]);
  }

  /**
   * Set license on user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   * @param \Drupal\taxonomy\Entity\Term[] $licenses
   *   The licenses.
   *
   * @see Drupal\tac_lite\Form\UserAccessForm::submitForm
   */
  private function setLicenses(UserInterface $user, array $licenses) {
    $this->userData->set('tac_lite', $user->id(), 'tac_lite_scheme_1', [
      'license' => array_map(static function (Term $term) {
        return $term->id();
      }, $licenses),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      LicenseFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['debile-djokes', 'user'];
  }

}
