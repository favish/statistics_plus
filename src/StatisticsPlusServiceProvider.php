<?php

namespace Drupal\statistics_plus;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class StatisticsPlusServiceProvider extends ServiceProviderBase {
  /**
   * {@inheritdoc}
   *
   * Alter the statistics storage service to use our extended class
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('statistics.storage.node');
    $definition->setClass('Drupal\statistics_plus\NodeStatisticsPlusDatabaseStorage');
  }

}