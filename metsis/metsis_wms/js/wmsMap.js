
(function($, Drupal, drupalSettings) {
    var sClient;
  Drupal.behaviors.metsisWmsMap = {
    attach: function(context, drupalSettings) {
      console.log("running attached function");
      $('.map', context).once('metsisWmsMap').each(function() {
        console.log("after first jquery");
        var lat = drupalSettings.metsis_wms.mapLat;
        var lon = drupalSettings.metsis_wms.mapLat;
        var zoom = drupalSettings.metsis_wms.zoom;
        var whichBaseLayer = drupalSettings.metsis_wms.whichBaseLayer;
        var overlayBorder = drupalSettings.metsis_wms.overlayBorder;
        var webMapServers = drupalSettings.metsis_wms.webMapServers;
        var productSelect = drupalSettings.metsis_wms.productSelect;

        console.log(webMapServers);
        var sClient;
        var wms = mapClient
          .wms({
            lon: lon,
            lat: lat,
            zoom: zoom,
            whichBaseLayer: whichBaseLayer,
            overlayBorder: overlayBorder,
            webMapServers: [
              webMapServers
            ],
            productSelect: productSelect
          });
      });


    },
  };
})(jQuery, Drupal, drupalSettings);

function reloadPage() {
  location.reload();
}
