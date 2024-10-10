(function ($, Drupal) {
  Drupal.behaviors.removeParentBehaviour = {
    attach: function (context, settings) {
      context.querySelectorAll('#remove-parent-filter').forEach((element) => {
        element.addEventListener('click', Drupal.behaviors.removeParentBehaviour.removeParentFilter);
      });
    },
    removeParentFilter: function () {
      // Your custom JavaScript function goes here.
      console.log('Div with id "remove-parent-filter" was clicked.');
      $('#edit-related-dataset-id--2').val('');
      $('#views-exposed-form-metsis-search-results').submit();
    }
  }
})(jQuery, Drupal);
