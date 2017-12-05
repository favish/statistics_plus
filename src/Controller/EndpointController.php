<?php

namespace Drupal\statistics_plus\Controller;

use Drupal\Core\Controller\ControllerBase;

use Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class EndpointController extends ControllerBase {

  /**
   * Provide endpoint for retrieving statistics data for several nodes at once
   */
  public function get(Request $request) {

    $entity_id_csv = $request->get('entity_ids');
    $entity_ids = explode(',', $entity_id_csv);

    $statistics_storage_service = \Drupal::service('statistics.storage.node');
    $fuzzed_view_counts = $statistics_storage_service->fetchFuzzedViewCounts($entity_ids);
    return new JsonResponse(
      $fuzzed_view_counts
    );
  }

}