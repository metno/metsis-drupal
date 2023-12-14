(function ($, once) {

  function throbberActivate() {
    if ($.trim($("#dash-loader-wrapper").html()) == '') {
      $('#dash-loader-wrapper').append('Dashboard is loading... <img id="dashTrobber" src="/core/misc/throbber-active.gif">');
    }
  }

  function throbberDeactivate() {
    $('#dash-loader-wrapper').empty();
  }

  /*
  document.addEventListener('readystatechange', event => {
    console.log('Document event: ' + event.target.readyState);
if (event.target.readyState === 'interactive') {
console.log("event target interactive");
throbberActivate();
}
else if (event.target.readyState === 'loading') {
console.log("event target loading");
throbberActivate();
}
else if (event.target.readyState === 'complete') {
console.log("event target complete");
//throbberDeactivate();
}

});*/
  Drupal.behaviors.bokehDashLoader = {
    attach: function (context) {
      $(once('#bokeh-dashboard', 'throbber-bokeh', context)).each(function () {
        //
        console.log("Initializing throbber bokeh behavior...");
        //Variable to hold dasbord/bokeh initialized or not
        var dash_init = false;

        //Override console log to add events when bokeh logs something
        var logFn = console.log;
        //The log function override
        console.log = function (arg1) {

          //First event if dashboard is not initalized
          if (!dash_init) {
            //Check if bokeh libraries are loaded and we have the bokeh object
            if (typeof Bokeh != "undefined") {
              logFn("Bokeh initialized");
              //logFn(Bokeh);
              //Set dashboard initialized to true.
              dash_init = true;
            }
          }
          //If Dasboard is initialized. we add some event listeners.
          if (dash_init) {
            let docs = Bokeh.documents;
            let doc = docs[0];
            //Check if we have the document object
            if (typeof doc != "undefined") {
              //logFn(doc);
              //logFn("Is doc idle? " + doc.is_idle);
              //Add the document ready event listender
              if (doc.is_idle) {
                throbberDeactivate();
              }
              else {
                throbberActivate();
              }
            }
            //KEEEP for logging to work.
            logFn(arg1);
          }
        }
        //Console error log override
        console.error = function (arg1) {
          // do your work on error part
          //logFn('error log');
          logFn(arg1);
          //logFn('your console hacked')
        }
      });
    },
    weight: 999
  }
})(jQuery, once);
