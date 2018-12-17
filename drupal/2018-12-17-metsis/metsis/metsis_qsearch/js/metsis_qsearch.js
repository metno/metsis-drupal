function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

(function ($) {
    Drupal.behaviors.metsisQsearchPagination = {
        //}anonymous function wrapper
        attach: function () {
            try {
                var hash = window.location.hash || "?page=1";
                var url = window.location.href;
                var parser = document.createElement('a');
                parser.href = url;
                //alert(parser.protocol); //e.g. https:
                //alert(parser.hostname); //e.g. xyz.metsis.met.no
                //alert(parser.port);     //e.g. 8080
                var path = parser.pathname; //e.g. /results/ or /child
                //alert(parser.search);   //e.g. ?page=1 or ?metadata_identifier=2d90033b-99dc-5a7a-a441-f9bd08ba233f&calling_results_page=https://xyz.metsis.met.no/results?page=1&page=8
                //alert(parser.hash);
                var params = url.match(/[(\?|\&)]([^=]+)\=([^&#]+)/g);
                //if url = "https://xyz.metsis.met.no/child?metadata_identifier=2d90033b-99dc-5a7a-a441-f9bd08ba233f&calling_results_page=https://xyz.metsis.met.no/results?page=1&page=8"
                //params[0] is then ?metadata_identifier=2d90033b-99dc-5a7a-a441-f9bd08ba233f
                //params[1] is then &calling_results_page=https://xyz.metsis.met.no/results?page=1
                //params[2] is then &page=8

                if (path == "/child") {
                    paginationHrefTextPrefix = params[0] + params[1] + "&page=";
                } else {
                    paginationHrefTextPrefix = "?page=";
                }
                var current_page = url.match(/\\?page=(\d+)$/);
                var currentPage = current_page[1];
                var number_of_pages = $('input[name="number_of_pages"]').val();
                $(".pagination").pagination({
                    ellipsePageSet: false,
                    //hrefTextPrefix: "?page=",
                    hrefTextPrefix: paginationHrefTextPrefix,
                    pages: number_of_pages,
                    currentPage: currentPage,
                    edges: 7,
                    cssStyle: 'light-theme'
                            //cssStyle: 'dark-theme'
                            //cssStyle: 'compact-theme'
                });
            } catch (e) {
            }
        }
        //anonymous function wrapper{
    };
}(jQuery));
//https://xyz.metsis.met.no/child  ?metadata_identifier=2d90033b-99dc-5a7a-a441-f9bd08ba233f  &calling_results_page=https://xyz.metsis.met.no/results?page=1  &page=8
(function ($) {
    Drupal.behaviors.metsisBackButton = {
        //}anonymous function wrapper
        attach: function () {
            try {
//                $('.adc-back').append('<div class="browser_back"><a class="browser_back_link" alt="go one page back" title="go one page back" href="#browser_back">back</a></div>');
                $('.adc-button .adc-back').click(function () {
                    alert("going back");
                    window.history.go(-1);
                    //document.metsis_qsearch_form.submit();
                    return false;
                });
            } catch (e) {
            }
        }
        //anonymous function wrapper{
    };
}(jQuery));

