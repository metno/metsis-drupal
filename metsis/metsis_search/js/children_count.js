(function ($, Drupal, once) {
  Drupal.behaviors.getChildrenCount = {
    attach: function (context) {
      // I am doing a find() but you can do a once() or whatever you like :-)
      //var metaIds = document.querySelectorAll('.views-field-id');
      //alert(metaIds);
      //    $(document, context).once('metsis_search').each(function() {
      // $(document).ready(function () {

      //$('#metachild',context).once('getChildrenCount').each(function() {
      // const childEl = once()
      $(once('#metachildlink', '.metachild')).each(function () {
        var reg = /(\<!--.*?\-->)/g;
        //var string = $(this).html();
        //var metaid = string.replace(reg,"").trim();
        var metaid = $(this).data("id");
        var isParent = $(this).attr("isparent");
        console.log(isParent);
        var myurl = '/metsis/elements/count?metadata_identifier=' + metaid;
        if (isParent) {
          console.log("Dataset: " + metaid + "has children.");
          //console.log(metaid);
          //console.log(isParent);
          //console.log(myurl);
          //if(isParent == "True") {
          $('#metachildlink', this).removeClass('visually-hidden');
          Drupal.ajax({
            url: myurl
          }).execute();
          //}
        }
      });
      // });
    },
    //};
  };
  //});

})(jQuery, Drupal, once);
