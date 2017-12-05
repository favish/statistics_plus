<?php

namespace Drupal\statistics;

/**
 * Value object for passing statistic results.
 */
class StatisticsPlusViewsResult {

  /**
   * @var int
   */
  protected $totalFuzzedCount;

  public function __construct($totalFuzzedCount) {
    $this->totalFuzzedCount = $totalFuzzedCount;
  }

  /**
   * Total number of times the entity has been viewed with fuzz.
   *
   * @return int
   */
  public function getFuzzedCount() {
    return $this->totalFuzzedCount;
  }

}
