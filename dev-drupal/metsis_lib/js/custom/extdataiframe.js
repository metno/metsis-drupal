
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
        //console.log(jQuery('.ext_data_source'));
        jQuery('.ext_data_source').click(function (event) {
            //hadcoding the href works, but is no good since we need to pass it 
            //in as a variable
            jQuery.colorbox({iframe:true, width:"80%", height:"80%", href: "https://xyz.metsis.met.no/ts?metadata_identifier=0b0b2aa5-7504-5cfc-bd44-aeb1b37f29e7"});

            
            
            //jQuery(".ext_data_source").css( "background-color", "red" );
            //var loc = jQuery(this).attr('href');
            //var finalURL = document.location.origin + loc;
            //event.preventDefault();
            //alert(finalURL);
            //var cbhtml='<iframe id="data_source_iframe" width="1000" height="800" src="' + finalURL + '" frameborder="0"></iframe>';
            //alert(cbhtml);
            //jQuery.colorbox({html: cbhtml});
            /**
             * test{
             */

            // looking for the first param to be 'v='
            // var id = loc.split("?")[1].split("&")[0].split("=")[1];
            //alert(JSON.stringify(jQuery));
            //alert(id);
            return false;

            //event.preventDefault();
            //jQuery.colorbox({closeButton: false, opacity: 0.9, html: '<iframe width="1000" height="800" src="' + jQuery(this).attr('href')+ '" frameborder="0"></iframe>'});
            //jQuery.colorbox({closeButton: false, opacity: 0.9, html: '<iframe width="1000" height="800" src="' + finalURL + '" frameborder="0"></iframe>'});
            // jQuery.colorbox({closeButton: false, opacity: 0.9, html: '<iframe id="data_source_iframe" width="1000" height="800" src="' + finalURL + '" frameborder="0"></iframe>'});
            /**
             * test}
             */

            
            //jQuery.colorbox({href: "https://xyz.metsis.met.no/ts?metadata_identifier=0b0b2aa5-7504-5cfc-bd44-aeb1b37f29e7", iframe: true});

            // jQuery.colorbox({html:'<iframe id="tds_frame" width="640" height="360" src="http://thredds.nersc.no/thredds/normap.html?dataset=' + id +'" frameborder="0" allowfullscreen></iframe>'});
            //jQuery.colorbox({html: '<iframe id="data_source_iframe" width="840" height="560" src="' + loc + '" frameborder="0" allowfullscreen></iframe>'});
            //jQuery.colorbox({closeButton: false, opacity: 0.9, html: '<iframe id="data_source_iframe" width="1000" height="800" src="' + finalURL + '" frameborder="0"></iframe>'});
        });
    });
//
})(jQuery);