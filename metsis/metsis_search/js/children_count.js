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
          var href = $('#metachildlink', this).attr('href');
          console.log(href);
          start_date = href.match(/(?:start_date=)(\d{4}-\d{2}-\d{2})/);
          if (start_date) {
            console.log(start_date[1]);
            myurl += '&start_date=' + start_date[1];
          }
          end_date = href.match(/(?:end_date=)(\d{4}-\d{2}-\d{2})/);
          if (end_date) {
            console.log(end_date[1]);
            myurl += '&end_date=' + end_date[1];
          }
          fulltext = href.match(/(fulltext=)[^&]+(?=&)/);
          if (fulltext) {
            //console.log(fulltext);
            fulltext_query = fulltext[0].replace(/fulltext=/, '');
            //console.log(fulltext_query.replace(/%20/, ' '));
            myurl += '&fulltext=' + fulltext_query.replace(/%20/, ' ');
          }

          fulltext_op = href.match(/(search_api_fulltext_op=)[^&]+(?=&)/);
          if (fulltext_op) {
            console.log(fulltext_op);
            fulltext_query_op = fulltext_op[0].replace(/search_api_fulltext_op=/, '');
            //console.log(fulltext_query.replace(/%20/, ' '));
            myurl += '&search_api_fulltext_op=' + fulltext_query_op;
          }

          console.log("Child count url: " + myurl);
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
