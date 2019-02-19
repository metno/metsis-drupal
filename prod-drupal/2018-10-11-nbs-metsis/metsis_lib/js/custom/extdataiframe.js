
/**
 * 
 * @param {type} $
 * @returns {undefined}
 * need to make this function more generic with appropriate arguments
 */
(function ($) {
    //prefer anonymous functions since these are more easily ported to other applications
    jQuery(function ($) {
        //alert(id);
        console.log(jQuery('.ext_data_source'));
        jQuery('.ext_data_source').click(function (event) {
            //jQuery(".ext_data_source").css( "background-color", "red" );
            var loc = jQuery(this).attr('href');
            // looking for the first param to be 'v='
            // var id = loc.split("?")[1].split("&")[0].split("=")[1];
            //alert(JSON.stringify(jQuery));
            //alert(id);



            event.preventDefault();
            // jQuery.colorbox({html:'<iframe id="tds_frame" width="640" height="360" src="http://thredds.nersc.no/thredds/normap.html?dataset=' + id +'" frameborder="0" allowfullscreen></iframe>'});
            //jQuery.colorbox({html: '<iframe id="data_source_iframe" width="840" height="560" src="' + loc + '" frameborder="0" allowfullscreen></iframe>'});
            jQuery.colorbox({closeButton: false, opacity: 0.9, html: '<iframe id="data_source_iframe" src="' + loc + '" frameborder="0"></iframe>'});
        });
    });
//
})(jQuery);