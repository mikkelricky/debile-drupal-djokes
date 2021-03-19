<?php

namespace Drupal\debile_djokes\Commands;

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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

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
    $this->entityTypeManager = $entityTypeManager;
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
   * @option collection-id
   *   The collection id.
   *
   * @command debile_djokes:import:djokes
   * @usage debile_djokes:import:djokes 'https://docs.google.com/spreadsheets/d/…/export?format=csv&gid=0' --collection-id=1
   */
  public function importDjokes(
    string $url,
    array $options = ['collection-id' => NULL]
  ) {
    if (!isset($options['collection-id'])) {
      throw new \RuntimeException('Missing option: collection-id');
    }

    $collectionId = $options['collection-id'];

    /** @var \Drupal\node\Entity\Node $collection */
    $collection = Node::load($collectionId);
    if (!$collection || 'collection' !== $collection->bundle()) {
      throw new \RuntimeException(sprintf('Invalid collection id: %s',
        $collectionId));
    }

    // Check if collection contains djokes.
    $djokes = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'djoke')
      ->condition('collection.target_id', $collection->id())
      ->execute();

    if (!empty($djokes)) {
      throw new \RuntimeException(sprintf('Collection %s (%d) is not empty',
        $collection->getTitle(), $collection->id()));
    }

    try {
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
        $djoke = Node::create([
          'type' => 'djoke',
          'collection' => [['target_id' => $collection->id()]],
          'status' => Node::PUBLISHED,
          'index' => $data['index'] ?? $data['id'],
          'djoke' => $data['djoke'] ?? $data['text'],
          'punchline' => $data['punchline'],
        ]);
        $djoke->save();
        $this->writeln($djoke->index->value);
      }
    }
  }

}
