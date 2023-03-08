(function ($, Drupal) {
    Drupal.behaviors.loadThumbnails = {
      attach: function (context, settings) {
      // I am doing a find() but you can do a once() or whatever you like :-)
      //var metaIds = document.querySelectorAll('.views-field-id');
      //alert(metaIds);
  //    $(document, context).once('metsis_search').each(function() {
  $(context).on('lazybeforeunveil', function (e) {
    var ajax = $(e.target).data('ajax');
    if(ajax){
      Drupal.ajax({ url: ajax
      }).execute();
        //$(e.target).load(ajax);
    }
});
        },
  //};
};
//});

})(jQuery, Drupal);
