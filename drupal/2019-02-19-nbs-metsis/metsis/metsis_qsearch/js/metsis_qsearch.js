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

/**
 * works as it should, but must be refactored! 
 */
(function ($) {
    Drupal.behaviors.metsisQSearch = {
        //{ anonymous function wrapper
        attach: function () {
            //Sentinel-1A
            $('#edit-platform-long-name-sentinel-1a-instrument-modes-chosen-instrument-modes-ew').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1a-instrument-modes-chosen-instrument-modes-iw').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-hh').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-hhhv').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-vv').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-vvvh').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').on('click', function (event) {
                if ($('#edit-platform-long-name-sentinel-1a-chosen-platform-long-name-sentinel-1a').prop('checked') == false) {
                    $('#edit-platform-long-name-sentinel-1a-instrument-modes-chosen-instrument-modes-ew').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1a-instrument-modes-chosen-instrument-modes-iw').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-hh').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-hhhv').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-vv').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1a-instrument-polarisations-chosen-instrument-polarisations-vvvh').prop('checked', false);
                }
            });
            //Sentinel-1B
            $('#edit-platform-long-name-sentinel-1b-instrument-modes-chosen-instrument-modes-ew').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1b-instrument-modes-chosen-instrument-modes-iw').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-hh').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-hhhv').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-vv').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-vvvh').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').on('click', function (event) {
                if ($('#edit-platform-long-name-sentinel-1b-chosen-platform-long-name-sentinel-1b').prop('checked') == false) {
                    $('#edit-platform-long-name-sentinel-1b-instrument-modes-chosen-instrument-modes-ew').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1b-instrument-modes-chosen-instrument-modes-iw').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-hh').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-hhhv').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-vv').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-1b-instrument-polarisations-chosen-instrument-polarisations-vvvh').prop('checked', false);
                }
            });
            //Sentinel-2A
            $('#edit-platform-long-name-sentinel-2a-product-types-chosen-product-types-l1c').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-product-types-chosen-product-types-l2a').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-10').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-20').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-30').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-40').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-50').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-60').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-70').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-80').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-90').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-90--2').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').on('click', function (event) {
                if ($('#edit-platform-long-name-sentinel-2a-chosen-platform-long-name-sentinel-2a').prop('checked') == false) {
                    $('#edit-platform-long-name-sentinel-2a-product-types-chosen-product-types-l1c').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-product-types-chosen-product-types-l2a').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-10').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-20').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-30').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-40').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-50').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-60').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-70').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-80').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-90').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2a-cloud-cover-value-chosen-cloud-cover-value-90--2').prop('checked', false);
                }
            });
            //Sentinel-2B
            $('#edit-platform-long-name-sentinel-2b-product-types-chosen-product-types-l1c').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-product-types-chosen-product-types-l2a').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-10').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-20').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-30').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-40').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-50').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-60').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-70').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-80').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-90').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-90--2').on('click', function (event) {
                $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked', true);
            });
            $('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').on('click', function (event) {
                if ($('#edit-platform-long-name-sentinel-2b-chosen-platform-long-name-sentinel-2b').prop('checked') == false) {
                    $('#edit-platform-long-name-sentinel-2b-product-types-chosen-product-types-l1c').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-product-types-chosen-product-types-l2a').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-10').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-20').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-30').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-40').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-50').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-60').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-70').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-80').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-90').prop('checked', false);
                    $('#edit-platform-long-name-sentinel-2b-cloud-cover-value-chosen-cloud-cover-value-90--2').prop('checked', false);
                }
            });
        }
        //anonymous function wrapper {
    };
}(jQuery));


/**
 * copy search URL{
 */
(function ($) {
    Drupal.behaviors.metsisQSearchShareSearch = {
        //}anonymous function wrapper
        attach: function () {
            $('#shared-search').on('click', function (event) {
                //alert($(this).attr('zulu') + " copied");
                /**
                 * test{ 
                 */
                $('.quid-share-string').toggle("slow");
//                var id = $(this).attr('id');
//                var el = document.getElementById(id);
//                var range = document.createRange();
//                range.selectNodeContents(el);
//                var sel = window.getSelection();
//                sel.removeAllRanges();
//                sel.addRange(range);
//                document.execCommand('copy');
//                alert("Contents copied to clipboard.");
//                return false;
                /**
                 * test} 
                 */

            });
        }
        //anonymous function wrapper{
    };
}(jQuery));
/**
 * copy search URL}
 */
/**
 * TODO 05
 * JS qsearch form reset button{
 */
/**
 * JS qsearch form reset button}
 */

//
//
//function copyToClipboard(element) {
//    var $temp = $("<input>");
//    $("body").append($temp);
//    $temp.val($(element).text()).select();
//    document.execCommand("copy");
//    //$temp.remove();
//}