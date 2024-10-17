(function ($, Drupal) {
  Drupal.behaviors.removeParentBehaviour = {
    attach: function (context, settings) {
      context.querySelectorAll('#remove-parent-filter').forEach((element) => {
        const search_view = settings.metsis_search.search_view;
        element.dataset.searchView = search_view; // Store the value as a data attribute on the element
        element.addEventListener('click', Drupal.behaviors.removeParentBehaviour.removeParentFilter);
      });
    },
    removeParentFilter: function (event) {
      const targetElement = event.target;
      const search_view = targetElement.dataset.searchView; // Retrieve the value from the data attribute
      console.log('Div with id "remove-parent-filter" was clicked.', search_view);
      if (search_view === 'metsis_search') {
        $('input[name="related_dataset_id"][data-drupal-selector="edit-related-dataset-id"]').val('');
        $('select[name="platform_ancillary_cloud_coverage"][data-drupal-selector="edit-platform-ancillary-cloud-coverage"]').val('');
        $('#views-exposed-form-metsis-search-results').submit();
      }
      if (search_view === 'metsis_simple_search') {
        $('input[name="related_dataset_id"][data-drupal-selector="edit-related-dataset-id"]').val('');
        $('#views-exposed-form-metsis-simple-search-results').submit();
      }
    }
  };
})(jQuery, Drupal);
