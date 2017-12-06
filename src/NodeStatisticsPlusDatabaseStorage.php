<?php

namespace Drupal\statistics_plus;

use Drupal\statistics\NodeStatisticsDatabaseStorage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\statistics\StatisticsViewsResult;

/**
 * Provides the default database storage backend for statistics.
 */
class NodeStatisticsPlusDatabaseStorage extends NodeStatisticsDatabaseStorage {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(Connection $connection, StateInterface $state, RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    parent::__construct($connection, $state, $request_stack);
  }
  
  /**
   * Fetch view counts based on fuzz configuration.

   * @param array $ids
   *   The ids of the entities to get counts for.
   * @return mixed - Return the resulting method invocation.
   */
  public function fetchViewCounts($ids) {
    $fetchMethod = 'fetchTotalViewCounts';

    $enable_fuzz = $this->configFactory->get('statistics_plus.settings')->get('enable_fuzz');
    if ($enable_fuzz) {
      $fetchMethod = 'fetchFuzzedViewCounts';
    }

    return $this->{$fetchMethod}($ids);
  }

  public function fetchViewCount($id) {
    $views = $this->fetchViewCounts([$id]);
    return reset($views);
  }

  /**
   * Get total view counts for one entity.
   *
   * @param int $id
   *   The ID of the entity to get view counts for.
   *
   * @return int
   *   Total number of views for the entity
   */
  public function fetchTotalViewCount($id) {
    $views = $this->fetchTotalViewCounts([$id]);
    return reset($views);
  }

  /**
   * Get total view counts for several entities.
   *
   * @param array $ids
   *   The ids of the entities to get counts for.
   *
   * @return array
   *   Array keyed by entity id with the view count as values.
   */
  public function fetchTotalViewCounts($ids) {
    $views = $this->connection
      ->select('node_counter', 'nc')
      ->fields('nc', ['nid', 'totalcount'])
      ->condition('nid', $ids, 'IN')
      ->execute()
      ->fetchAllAssoc('nid');
    $map = function ($row) {
      return $row->totalcount;
    };
    return array_map($map, $views);
  }

  public function fetchFuzzedViewCount($id) {
    $views = $this->fetchViews([$id]);
    return reset($views);
  }

  public function fetchFuzzedViewCounts($ids) {
    $views = $this->connection
      ->select('node_counter', 'nc')
      ->fields('nc', ['nid', 'totalfuzzcount'])
      ->condition('nid', $ids, 'IN')
      ->execute()
      ->fetchAllAssoc('nid');
    // If no views were found for an ID, return 0
    $total_counts = array_map(
      function ($id) use ($views) {
        if (array_key_exists($id, $views)) {
          return $views[$id]->totalfuzzcount;
        } else {
          return 0;
        }
      },
      $ids
    );
    $views_by_id = array_combine($ids, $total_counts);
    return $views_by_id;
  }

  /**
   * Returns integer for amount of 'hot' fuzz to add for this view
   * Hot fuzz is added for nodes during the first five minutes of their creation
   * @param int $id
   * @return int fuzz amount to add
   */
  protected function getHotFuzz($id) {
    // Get creation time for this node
    $created = $this->connection
      ->select('node_field_data', 'nfd')
      ->fields('nfd', ['created'])
      ->condition('nid', $id)
      ->execute()
      ->fetchField();
    // TODO - make the amount of time configurable - MEA
    $addHotFuzz = ($this->getRequestTime() - $created) < 600;
    return $addHotFuzz ? 1 : 0;
  }

  private function getRandomFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
  }

  /**
   * Returns integer for amount of 'random' fuzz to add for this view
   * Random fuzz is added after a roll to potentially double the view
   * @param $id
   * @return int fuzz amount to add
   */
  protected function getRandomFuzz($id) {
    // TODO - Make probability configurable - MEA
    $chance = 0.8;
    $roll = $this->getRandomFloat(0, 1);
    $addRandomFuzz = $roll <= $chance;
    return $addRandomFuzz ? 1 : 0;
  }

  /**
   * {@inheritdoc}
   * Uptick fuzzed view counts
   */
  public function recordView($id) {
    // Calc fuzz amounts here to re-use below
    $hotFuzzAmount = $this->getHotFuzz($id);
    $randomFuzzAmount = $this->getRandomFuzz($id);
    // All fuzz amounts plus 1 for the normal count
    $totalFuzzAmount = $hotFuzzAmount + $randomFuzzAmount + 1;

    return (bool) $this->connection
      ->merge('node_counter')
      ->key('nid', $id)
      ->fields([
        'daycount' => 1,
        'totalcount' => 1,
        'hotfuzzcount' => 0,
        'randomfuzzcount' => 0,
        'totalfuzzcount' => 1,
        'timestamp' => $this->getRequestTime(),
      ])
      ->expression('daycount', 'daycount + 1')
      ->expression('totalcount', 'totalcount + 1')
      ->expression('hotfuzzcount', 'hotfuzzcount + ' . $hotFuzzAmount)
      ->expression('randomfuzzcount', 'randomfuzzcount + ' . $randomFuzzAmount)
      ->expression('totalfuzzcount', 'totalfuzzcount + ' . $totalFuzzAmount)
      ->execute();
  }

  /**
   * {@inheritdoc}
   * Fixed to actually key by id
   */
  public function fetchViews($ids) {
    $views = $this->connection
      ->select('node_counter', 'nc')
      ->fields('nc', ['nid', 'totalcount', 'daycount', 'timestamp'])
      ->condition('nid', $ids, 'IN')
      ->execute()
      ->fetchAll();
    foreach ($views as $index => $view) {
      $views[$view->nid] = new StatisticsViewsResult($view->totalcount, $view->daycount, $view->timestamp);
    }
    return $views;
  }
}
