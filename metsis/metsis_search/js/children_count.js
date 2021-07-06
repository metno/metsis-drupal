(function ($, Drupal) {
    Drupal.behaviors.getChildrenCount = {
      attach: function (context, settings) {
      // I am doing a find() but you can do a once() or whatever you like :-)
      //var metaIds = document.querySelectorAll('.views-field-id');
      //alert(metaIds);
  //    $(document, context).once('metsis_search').each(function() {
  $( document ).ready(function() {

        //$('#metachild',context).once('getChildrenCount').each(function() {
          $('.metachild').once().each(function() {
          var reg = /(\<!--.*?\-->)/g;
          //var string = $(this).html();
          //var metaid = string.replace(reg,"").trim();
          var metaid = $(this).data("id");
          var isParent = $(this).attr("isparent");
          var myurl = '/metsis/elements/count?metadata_identifier='+metaid;
          console.log("Dataset: " + metaid + "has children." );
          //console.log(metaid);
          //console.log(isParent);
          //console.log(myurl);
          //if(isParent == "True") {
            $('#metachildlink', this).removeClass('visually-hidden');
            Drupal.ajax({ url: myurl
            }).execute();
          //}


          });
        });
        },
  //};
};
//});

})(jQuery, Drupal);
