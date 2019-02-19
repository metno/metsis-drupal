(function ($) {
    //}anonymous function wrapper
    Drupal.behaviors.metsisFormReset = {
        attach: function (context, settings) {
            $("#edit-reset").click(function () {
                console.log("You clicked!");
                // alert("you clicked");
            });
            /*
             $(".form-reset-button").submit(function (event) {
             event.preventDefault();
             alert("not submitting");
             });
             */
        }
    };
    //anonymous function wrapper{
}(jQuery));


/*
 * form reset{
 */
/*
 (function ($) {
 //}anonymous function wrapper
 Drupal.behaviors.search_form_reset = {
 attach: function (context, settings) {
 // $("#metssi-search-e-form").
 // Prevent form submission
 $("#metsis-search-e-form").submit(function (event) {
 event.preventDefault();
 });
 }
 };
 //anonymous function wrapper{
 }(jQuery));
 */
/*
 * form reset}
 */


/*
 * 
 * 
 (function ($) {
 //}anonymous function wrapper
 
 $(function () {
 $("#datepicker").datepicker();
 });
 
 //anonymous function wrapper{
 }(jQuery));
 */


/*
 * fieldset functions{
 */
/*
 (function ($) {
 //}anonymous function wrapper
 
 $('legend').click(function () {
 $('fieldset').toggleClass('active');
 });
 
 //anonymous function wrapper{
 }(jQuery));
 */
/*
 * fieldset functions}
 */
/*
 * datepicker{
 */
/*
 (function ($) {
 //}anonymous function wrapper
 
 $(function () {
 var dateFormat = "mm/dd/yy",
 from = $("#from")
 .datepicker({
 defaultDate: "+1w",
 changeMonth: true,
 numberOfMonths: 3
 })
 .on("change", function () {
 to.datepicker("option", "minDate", getDate(this));
 }),
 to = $("#to").datepicker({
 defaultDate: "+1w",
 changeMonth: true,
 numberOfMonths: 3
 })
 .on("change", function () {
 from.datepicker("option", "maxDate", getDate(this));
 });
 
 function getDate(element) {
 var date;
 try {
 date = $.datepicker.parseDate(dateFormat, element.value);
 }
 catch (error) {
 date = null;
 }
 
 return date;
 }
 });
 
 //anonymous function wrapper{
 }(jQuery));
 */
/*
 * datepicker}
 */

/*
 * test{
 */
/*
 (function ($) {
 Drupal.behaviors.formTheme = {
 attach: function () {
 var defaults = [];
 defaults["#datepicker"] = Drupal.t("First Name");
 defaults["#edit-last-name"] = Drupal.t("Last Name");
 defaults["#edit-age"] = Drupal.t("Age");
 var element;
 for (element in defaults) {
 if (defaults.hasOwnProperty(element)) {
 $(element).datepicker();
 $(element).val(defaults[element]).css("color", "grey").focus(function () {
 var key = "#" + $(this).attr("id");
 if ($(this).val() === defaults[key]) {
 $(this).css("color", "black").val("");
 }
 }).blur(function () {
 if ($(this).val() == "") {
 var key = "#" + $(this).attr("id");
 $(this).css("color", "grey").val(defaults[key]);
 }
 });
 }
 }
 }
 };
 }(jQuery));
 */
/*
 * test}
 */
