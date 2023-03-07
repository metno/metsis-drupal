(function ($) {
    Drupal.behaviors.displayInColorbox = {
        //}anonymous function wrapper
        attach: function () {

            try {
                //                $('.adc-back').append('<div class="browser_back"><a class="browser_back_link" alt="go one page back" title="go one page back" href="#browser_back">back</a></div>');
                $('.ext_data_source').click(function () {
                    var loc = jQuery(this).attr('href');
                    alert(loc);
                    jQuery.colorbox({ html: "<h1>Welcome</h1>" });
                    event.preventDefault();
                    //jQuery.colorbox({closeButton: true, opacity: 0.9, html: '<iframe id="data_source_iframe" src="' + loc + '" frameborder="0" allowfullscreen></iframe>'});
                    //return;
                });
            } catch (e) {
            }
        }
        //anonymous function wrapper{
    };
}(jQuery));
//
//
//(function ($) {
//    //prefer anonymous functions since these are more easily ported to other applications
//    jQuery(function ($) {
//        jQuery(document).ready(function () {
//            jQuery('.ext_data_source').click(function (event) {
//                alert("laskjdfal;skjfa;lfkjas");
//                //var loc = jQuery(this).attr('href');
//                //event.preventDefault();
//                //jQuery.colorbox({closeButton: true, opacity: 0.9, html: '<iframe id="data_source_iframe" src="' + loc + '" frameborder="0" allowfullscreen></iframe>'});
//            })
//        });
//    });
////prefer anonymous functions since these are more easily ported to other applications
//})(jQuery);
