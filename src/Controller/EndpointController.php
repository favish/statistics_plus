<?php

namespace Drupal\statistics_plus\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class EndpointController extends ControllerBase {

  /**
   * The page cache kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Constructs a new EndpointController object.
   *
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $kill_switch
   *   The page cache kill switch.
   */
  public function __construct(KillSwitch $kill_switch) {
    $this->killSwitch = $kill_switch;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * Provide endpoint for retrieving statistics data for several nodes at once
   */
  public function get(Request $request) {

    $entity_id_csv = $request->get('entity_ids');
    $entity_ids = explode(',', $entity_id_csv);

    $storage = \Drupal::service('statistics.storage.node');
    $view_counts = $storage->fetchViewCounts($entity_ids);

    $this->killSwitch->trigger();

    return new JsonResponse($view_counts);
  }

}