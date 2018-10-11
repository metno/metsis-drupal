(function ($) {
    //prefer anonymous functions since these are more easily ported to other applications
    jQuery(function ($) {
        jQuery(document).ready(function () {
            jQuery('.ext_data_source').click(function (event) {
                var loc = jQuery(this).attr('href');
                event.preventDefault();
                jQuery.colorbox({closeButton: true, opacity: 0.9, html: '<iframe id="data_source_iframe" src="' + loc + '" frameborder="0" allowfullscreen></iframe>'});
            })
        });
    });
//prefer anonymous functions since these are more easily ported to other applications
})(jQuery);