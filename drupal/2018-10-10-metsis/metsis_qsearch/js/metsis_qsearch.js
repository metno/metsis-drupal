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
                var current_page = url.match(/\\?page=(\d+)$/);
                var currentPage = current_page[1];
                var number_of_pages = $('input[name="number_of_pages"]').val();
                $(".pagination").pagination({
                    ellipsePageSet: false,
                    hrefTextPrefix: "?page=",
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

