(function($, Drupal) {
  Drupal.behaviors.statistics_ajax = {
    attach: function (context) {
      var $counters = $('[data-statistics]', context);
      if ($counters.length == 0) return;
      var counterElementsById = {};
      $counters.each(function (index, element) {
        var $element = $(element);
        var entityId = $element.attr('data-entityid');
        if (entityId) {
          counterElementsById[$element.attr('data-entityid')] = $element;
        } else {
          console.warn('Unable to bind statistics to element.', $element);
        }
      });
      $.ajax({
        url: '/statistics-ajax?entity_ids=' + Object.keys(counterElementsById).join(','),
        success: function(viewCountsById) {
          if (viewCountsById) {
            var nodeIds = Object.keys(viewCountsById);
            nodeIds.forEach(function(nodeId) {
              counterElementsById[nodeId].text(viewCountsById[nodeId]);
              counterElementsById[nodeId].append(
                '<i class="fa fa-eye" aria-hidden="true" style="margin-left:0.25em; font-weight: bold; display: inline"></i>'
              );
            })
          }
        },
        error: function(error) {
          console.error(error);
        }
      })
    }
  }
})(jQuery, Drupal);
