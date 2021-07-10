<?php

namespace Drupal\debile_djokes_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\node\Entity\Node;

/**
 * Collection fixture.
 *
 * @package Drupal\debile_djokes_fixtures\Fixture
 */
class CollectionFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   Filesystem.
   */
  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $fixtures = [
      1 => [
        'alias' => '500-debile-djokes',
        'title' => '500 Debile Djokes',
        'message_anonymous' => [
          'value' => '<p><a href="/user/login">Sign in</a> to read more djokes.</p>',
          'format' => 'rich_text',
        ],
        'message_unlicensed' => [
          'value' => '<p><a href="/license/500-debile-djokes">Buy license</a> to read all djokes.</p>',
          'format' => 'rich_text',
        ],
      ],
      2 => [
        'alias' => '500-nye-debile-djokes',
        'title' => '500 nye Debile Djokes',
        'message_anonymous' => [
          'value' => '<p><a href="/user/login">Sign in</a> to read more djokes.</p>',
          'format' => 'rich_text',
        ],
        'message_unlicensed' => [
          'value' => '<p><a href="/license/500-nye-debile-djokes">Buy license</a> to read all djokes.</p>',
          'format' => 'rich_text',
        ],
      ],
    ];

    foreach ($fixtures as $nid => $fixture) {
      ['alias' => $alias] = $fixture;
      $destinationPath = 'public://' . $alias;
      $this->fileSystem->mkdir($destinationPath, 0755, TRUE);

      $cover = file_save_data(
        file_get_contents(__DIR__ . '/../../assets/' . $alias . '/cover.png'),
        $destinationPath . '/cover.png',
        FileSystemInterface::EXISTS_REPLACE
      );
      $icon = file_save_data(
        file_get_contents(__DIR__ . '/../../assets/' . $alias . '/icon.png'),
        $destinationPath . '/icon.png',
        FileSystemInterface::EXISTS_REPLACE
      );
      $logo = file_save_data(
        file_get_contents(__DIR__ . '/../../assets/' . $alias . '/logo.png'),
        $destinationPath . '/logo.png',
        FileSystemInterface::EXISTS_REPLACE
      );

      $collection = Node::create($fixture + [
        'nid' => $nid,
        'type' => 'collection',
        'status' => Node::PUBLISHED,
        'path' => [
          'alias' => '/' . $alias,
        ],
        'cover_image' => [
          'target_id' => $cover->id(),
        ],
        'icon' => [
          'target_id' => $icon->id(),
        ],
        'logo' => [
          'target_id' => $logo->id(),
        ],
      ]);

      $this->setReference('collection:' . $alias, $collection);
      $collection->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['debile-djokes', 'collection'];
  }

}
