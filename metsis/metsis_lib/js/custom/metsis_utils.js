(function ($) {
    Drupal.behaviors.custom_scripts = {
        attach: function (context) {
            var $adc_form_defaults = $adc_get_GET();

            //to enable autosubmit for any of the forms, add it's id here.
            //then pass in the key value pair in the page
            //request adc_qcache_submit_set=2357
            //see below. Note the "-" (hypen) instead of "_" (underscore)
            if ($adc_form_defaults['adc_qcache_submit_set'] == 2357) {
                if (document.getElementById("metsis-qsearch-form") !== NULL) {
                    document.getElementById("metsis-qsearch-form").submit();
                }
                if (document.getElementById("ts-config-form") !== NULL) {
                    document.getElementById("ts-config-form").submit();
                }
            }
        }
    }
})(jQuery);



//local helpers
function $adc_get_GET(param) {
    var vars = {};
    window.location.href.replace(location.hash, '').replace(
        /[?&]+([^=&]+)=?([^&]*)?/gi,
        function (m, key, value) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );

    if (param) {
        return vars[param] ? vars[param] : NULL;
    }
    return vars;
}
