<?php

namespace Drupal\debile_djokes\Commands;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;

/**
 * A drush command file.
 *
 * @package Drupal\debile_djokes\Commands
 */
class Commands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $nodeStorage;

  /**
   * The client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private ClientInterface $client;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    ClientInterface $client
  ) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->client = $client;
  }

  /**
   * Import djokes.
   *
   * @param string $url
   *   The CSV data url.
   * @param array $options
   *   The options.
   *
   * @option int collection-id
   *   The collection id.
   * @option bool update
   *   Update djokes in collection.
   *
   * @command debile_djokes:import:djokes
   * @usage debile_djokes:import:djokes 'https://docs.google.com/spreadsheets/d/…/export?format=csv&gid=0' --collection-id=1
   * @usage debile_djokes:import:djokes 'https://docs.google.com/spreadsheets/d/…/export?format=csv&gid=0' --collection-id=1 --update
   */
  public function importDjokes(
    string $url,
    array $options = ['collection-id' => NULL, 'update' => FALSE]
  ) {
    if (!isset($options['collection-id'])) {
      throw new \RuntimeException('Missing option: collection-id');
    }

    $collectionId = $options['collection-id'];
    $update = $options['update'];

    /** @var \Drupal\node\Entity\Node|null $collection */
    $collection = $this->nodeStorage->load($collectionId);
    if (NULL === $collection || 'collection' !== $collection->bundle()) {
      throw new \RuntimeException(sprintf('Invalid collection id: %s',
        $collectionId));
    }

    // Check if collection contains djokes.
    $djokeIds = $this->nodeStorage
      ->getQuery()
      ->condition('type', 'djoke')
      ->condition('collection.target_id', $collection->id())
      ->execute();

    if (!$update && !empty($djokeIds)) {
      throw new \RuntimeException(sprintf('Collection %s (%d) is not empty',
        $collection->getTitle(), $collection->id()));
    }

    // Index existing djokes by index.
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $this->nodeStorage->loadMultiple($djokeIds);
    $djokes = [];
    foreach ($nodes as $djoke) {
      $djokes[$djoke->get('index')->getString()] = $djoke;
    }

    try {
      $this->writeln(sprintf('Loading data from %s', $url));
      $response = $this->client->request('GET', $url);
      $data = (string) $response->getBody();
    }
    catch (ClientException $e) {
      throw new \RuntimeException(sprintf('Cannot get data from url %s', $url));
    }

    $lines = array_map('trim', explode(PHP_EOL, $data));
    $header = NULL;
    foreach ($lines as $line) {
      $row = str_getcsv($line);
      if (NULL === $header) {
        $header = $row;
      }
      else {
        $data = array_combine($header, $row);
        $index = $data['index'] ?? $data['id'];
        /** @var \Drupal\node\Entity\Node|null $djoke */
        $djoke = $djokes[$index] ?? NULL;
        if (NULL === $djoke) {
          $djoke = Node::create([
            'type' => 'djoke',
          ]);
        }
        $djoke
          ->set('collection', [['target_id' => $collection->id()]])
          ->set('status', Node::PUBLISHED)
          ->set('index', $data['index'] ?? $data['id'])
          ->set('djoke', $data['djoke'] ?? $data['text'])
          ->set('punchline', $data['punchline'])
          ->save();

        $this->writeln(sprintf('% 4d: %s', $djoke->get('index')->getString(), $djoke->getTitle()));
      }
    }
  }

}
