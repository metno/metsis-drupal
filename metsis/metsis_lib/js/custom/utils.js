/**
 * Drupal AJAX forms are submitted by the mousedown event instead of the click
 * event to make these forms work with form fields like autocomplete. The
 * downside is that forms are not submitted on an enter keypress. See
 * drupal.org/node/634616, drupal.org/node/216059 and drupal.org/node/1403614
 * for more information.
 *
 * This jQuery v1.7+ solution uses .on() to bind the keypress event to *all*
 * input fields that are in the DOM right now or inserted later and as long as
 * they do not have the following CSS classes:
 *  - .prevent-submit-on-enter
 *  - .form-autocomplete
 *
 * When the enter key is pressed on one of these fields, this script searches
 * for a submit button with the .submit-on-enter class to trigger the mousedown
 * event on, otherwise the first submit button is used.
 *
 * Posted at drupal.org/node/1403614#comment-8718725.
 * See also: https://www.drupal.org/node/1403614#comment-10496896
 */
//jQuery(function ($) {
//    $(document.body).on(
//            'keypress',
//            'input:not(.prevent-submit-on-enter, .form-autocomplete)',
//            function (e) {
//                // If keypress enter.
//                if (e.keyCode == 13) {
//                    // Get form as jQuery object.
//                    var $form = $(this.form);
//                    //alert($form);
//                    // If form has a button with the .submit-on-enter class.
//                    if ($form.find('.submit-on-enter:first').length) {
//                        // Trigger mousedown event on this button.
//                        $form.find('.submit-on-enter:first').trigger(
//                                'mousedown');
//                    } else {
//                        // Trigger mousedown event on the first submit button in
//                        // the form.
//                        $form.find('[type="submit"]:first')
//                                .trigger('mousedown');
//                    }
//                }
//            });
/**
 * test to make sure we're traversing the DOM.
 * Uncommenting this one line should put a read thick border around ALL divs on page!
 * $("html").find("*").css({"color": "red", "border": "2px solid red"});
 */
//});

/**
 * back button
 */
(function ($) {
    //prefer anonymous functions since these are more easily ported to other applications
    jQuery(function ($) {
        jQuery('a.adc-back').click(function () {
            parent.history.back();
            return FALSE;
        });
    });
    //
})(jQuery);

/**
 * test{
 * NOT ready for use.
 *
 * needs
 * code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css
 * and
 * code.jquery.com/ui/1.12.1/jquery-ui.js
 *
 */
/*

(function ($) {
    //prefer anonymous functions since these are more easily ported to other applications
    jQuery(function () {
        jQuery("#slider-range").slider({
            range: true,
            min: 0,
            max: 500,
            values: [75, 300], //start and stop dates from OPeNDAP must be passed in here
            slide: function (event, ui) {
                jQuery("#amount").val("" + ui.values[ 0 ] + " - " + ui.values[ 1 ]);
            }
        });
        jQuery("#amount").val("$" + $("#slider-range").slider("values", 0) +
                " - " + jQuery("#slider-range").slider("values", 1));
    });
//
})(jQuery);

*/
/**
 * test}
 */
