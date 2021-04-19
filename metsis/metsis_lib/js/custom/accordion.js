(function ($) {
    //
    $(document).ready(function () {
        $(".datasets tr:not(.accordion)").hide();
        $(".datasets tr:first-child").show();
        $(".datasets tr.accordion").click(function () {
            $(this).nextAll("tr").fadeToggle("fast");
            // alert("click event on row");
        });
    });
    //  
})(jQuery);

