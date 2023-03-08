(function ($, Drupal, drupalSettings) {
//Drupal.behaviors.metsismapsearch = {
//  attach: function (context, drupalSettings) {
//    $('.map-search', context).once('example-behavior').each(function() {
$(document).ready(function () {
      var lat = drupalSettings.metsis_search.mapLat;
      var lon = drupalSettings.metsis_search.mapLon;
      var defzoom = drupalSettings.metsis_search.mapZoom;
      //var lon = Drupal.settings.lon;
//var lat = Drupal.settings.lat;
//var defzoom = Drupal.settings.zoom;
var init_proj = drupalSettings.metsis_search.init_proj;
var additional_layers = drupalSettings.metsis_search.additional_layers;
console.log("Start of searchmap.js script");

//jQuery.extend(drupalSettings, {"basePath":"\/","pathPrefix":"","ajaxPageState":{"theme":"corporateclean","theme_token":"gNMyXZLSUEwumabm8batFwoR_gUi8IjMk43e7oh5vKQ","js":{"0":1,"sites\/all\/modules\/jquery_update\/replace\/jquery\/1.10\/jquery.min.js":1,"misc\/jquery-extend-3.4.0.js":1,"misc\/jquery-html-prefilter-3.5.0-backport.js":1,"misc\/jquery.once.js":1,"misc\/drupal.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.core.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/misc\/1.9\/jquery.ba-bbq.min.js":1,"sites\/all\/modules\/jquery_update\/replace\/misc\/1.9\/overlay-parent.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/ui\/minified\/jquery.ui.datepicker.min.js":1,"sites\/all\/modules\/date\/date_popup\/jquery.timeentry.pack.js":1,"modules\/contextual\/contextual.js":1,"sites\/all\/modules\/jquery_update\/replace\/ui\/external\/jquery.cookie.js":1,"misc\/form.js":1,"sites\/all\/modules\/project_issue\/js\/project-issue.js":1,"sites\/adc.met.no\/themes\/corporateclean\/js\/jquery.cycle.all.js":1,"sites\/all\/modules\/metsis\/metsis_lib\/js\/custom\/utils.js":1,"sites\/all\/modules\/metsis\/metsis_lib\/js\/custom\/metsis_utils.js":1,"sites\/all\/modules\/metsis\/metsis_wms\/js\/bundle.js":1,"sites\/all\/libraries\/openlayers6\/build\/ol.js":1,"https:\/\/cdnjs.cloudflare.com\/ajax\/libs\/proj4js\/2.3.15\/proj4.js":1,"sites\/all\/modules\/date\/date_popup\/date_popup.js":1,"misc\/collapse.js":1,"misc\/autocomplete.js":1,"\/sites\/all\/modules\/jquery_update\/replace\/jquery\/1.10\/jquery.min.js":1,"file":1,"sites\/all\/modules\/metsis\/metsis_qsearch\/js\/metsis_qsearch.js":1,"modules\/toolbar\/toolbar.js":1},"css":{"modules\/system\/system.base.css":1,"modules\/system\/system.menus.css":1,"modules\/system\/system.messages.css":1,"modules\/system\/system.theme.css":1,"misc\/ui\/jquery.ui.core.css":1,"misc\/ui\/jquery.ui.theme.css":1,"modules\/overlay\/overlay-parent.css":1,"misc\/ui\/jquery.ui.datepicker.css":1,"sites\/all\/modules\/date\/date_popup\/themes\/jquery.timeentry.css":1,"modules\/contextual\/contextual.css":1,"sites\/all\/modules\/calendar\/css\/calendar_multiday.css":1,"modules\/comment\/comment.css":1,"sites\/all\/modules\/date\/date_api\/date.css":1,"sites\/all\/modules\/date\/date_popup\/themes\/datepicker.1.7.css":1,"modules\/field\/theme\/field.css":1,"modules\/node\/node.css":1,"sites\/all\/modules\/project_issue\/project_issue.css":1,"sites\/all\/modules\/project\/release\/project_release.css":1,"modules\/search\/search.css":1,"modules\/user\/user.css":1,"sites\/all\/modules\/views\/css\/views.css":1,"sites\/all\/modules\/ckeditor\/css\/ckeditor.css":1,"sites\/all\/modules\/ctools\/css\/ctools.css":1,"sites\/all\/modules\/dataset_validation\/css\/dataset_validation_style_cc.css":1,"sites\/adc.met.no\/modules\/landing_page_creator\/css\/landing_page_creator.css":1,"sites\/all\/modules\/metsis\/metsis_csv\/css\/metsis_csv_responsive.css":1,"sites\/all\/modules\/metsis\/metsis_csv_bokeh\/css\/metsis_csv_bokeh.css":1,"sites\/all\/modules\/metsis\/metsis_wms\/css\/style.min.css":1,"sites\/all\/modules\/metsis\/metsis_lib\/css\/tables.css":1,"sites\/all\/modules\/metsis\/metsis_lib\/css\/metsis_lib.css":1,"sites\/all\/modules\/metsis\/metsis_lib\/css\/icons.css":1,"sites\/all\/modules\/metsis\/metsis_lib\/css\/adc-buttons.css":1,"sites\/all\/modules\/metsis\/metsis_lib\/css\/autocomplete.css":1,"sites\/all\/modules\/metsis\/metsis_lib\/css\/map_thumbnails.css":1,"sites\/all\/modules\/metsis\/metsis_lib\/css\/popups.css":1,"sites\/all\/modules\/metsis\/metsis_qsearch\/css\/qstyles.css":1,"sites\/all\/modules\/metsis\/metsis_qsearch\/css\/metsis_qsearch_misc.css":1,"sites\/all\/modules\/metsis\/metsis_timeseries\/css\/metsis_timeseries_responsive.css":1,"sites\/all\/modules\/metsis\/metsis_ts_bokeh\/css\/metsis_ts_bokeh.css":1,"sites\/all\/libraries\/openlayers6\/css\/ol.css":1,"file":1,"sites\/all\/modules\/metsis\/metsis_qsearch\/css\/metsis_qsearch.css":1,"modules\/shortcut\/shortcut.css":1,"modules\/toolbar\/toolbar.css":1,"modules\/toolbar\/toolbar-print.css":1,"sites\/adc.met.no\/themes\/corporateclean\/css\/960.css":1,"sites\/adc.met.no\/themes\/corporateclean\/css\/720.css":1,"sites\/adc.met.no\/themes\/corporateclean\/css\/mobile.css":1,"sites\/adc.met.no\/themes\/corporateclean\/style.css":1,"sites\/adc.met.no\/themes\/corporateclean\/color\/colors.css":1,"sites\/adc.met.no\/themes\/corporateclean\/css\/basic-layout.css":1,"sites\/adc.met.no\/themes\/corporateclean\/css\/ie.css":1}},"overlay":{"paths":{"admin":"node\/*\/webform\nnode\/*\/webform\/*\nnode\/*\/webform-results\nnode\/*\/webform-results\/*\nnode\/*\/submission\/*\nfield-collection\/*\/*\/edit\nfield-collection\/*\/*\/delete\nfield-collection\/*\/add\/*\/*\nnode\/*\/edit\nnode\/*\/delete\nnode\/*\/revisions\nnode\/*\/revisions\/*\/revert\nnode\/*\/revisions\/*\/delete\nnode\/add\nnode\/add\/*\noverlay\/dismiss-message\nuser\/*\/shortcuts\nadmin\nadmin\/*\nbatch\ntaxonomy\/term\/*\/edit\nuser\/*\/cancel\nuser\/*\/edit\nuser\/*\/edit\/*","non_admin":"admin\/structure\/block\/demo\/*\nadmin\/reports\/status\/php"},"pathPrefixes":[],"ajaxCallback":"overlay-ajax"},"lon":15.64685556,"lat":78.22314167,"zoom":4,"init_proj_map":"EPSG:4326","additional_layers":false,"datePopup":{"edit-finished-after-datepicker-popup-0":{"func":"datepicker","settings":{"changeMonth":true,"changeYear":true,"autoPopUp":"focus","closeAtTop":false,"speed":"immediate","firstDay":1,"dateFormat":"yy-mm-dd","yearRange":"-50:+2","fromTo":false,"defaultDate":"0y"}},"edit-finished-before-datepicker-popup-0":{"func":"datepicker","settings":{"changeMonth":true,"changeYear":true,"autoPopUp":"focus","closeAtTop":false,"speed":"immediate","firstDay":1,"dateFormat":"yy-mm-dd","yearRange":"-50:+2","fromTo":false,"defaultDate":"0y"}}},"urlIsAjaxTrusted":{"\/":true},"tableHeaderOffset":"Drupal.toolbar.height"});

// 32661
proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
ol.proj.proj4.register(proj4);
var ext32661 = [-4e + 06, -3e + 06, 8e + 06, 8e + 06];
var center32661 = [15, 70];
var proj32661 = new ol.proj.Projection({
  code: 'EPSG:32661',
  extent: ext32661
});

// 32761
proj4.defs('EPSG:32761', '+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +ellps=WGS84 +datum=WGS84 +units=m +no_defs');
ol.proj.proj4.register(proj4);
var ext32761 = [-8e + 06, -8e + 06, 12e + 06, 10e + 06];
var center32761 = [15, -90];
var proj32761 = new ol.proj.Projection({
  code: 'EPSG:32761',
  extent: ext32761
});

// 4326
var ext4326 = [-350.0000, -90.0000, 350.0000, 90.0000];
var center4326 = [15, 70];
var proj4326 = new ol.proj.Projection({
  code: 'EPSG:4326',
  extent: ext4326
});

projObjectforCode = {
  'EPSG:4326': {extent: ext4326, center: center4326, projection: proj4326},
  'EPSG:32661': {extent: ext32661, center: center32661, projection: proj32661},
  'EPSG:32761': {extent: ext32761, center: center32761, projection: proj32761}
};

var ch = document.getElementsByName('map-search-projection');

document.getElementById(init_proj).checked = TRUE;

for (var i = ch.length; i--;) {
  ch[i].onchange = function change_projection() {
    var prj = this.value;
    for (var j = map.getLayers().getArray().length; j > 1; j--) {
      map.getLayers().removeAt(j - 1);
    }
    if (prj == 'EPSG:32761') {
      map.getLayers().removeAt(0, layer['baseN']);
      map.getLayers().insertAt(0, layer['baseS']);
    }
    else {
      map.getLayers().removeAt(0, layer['baseS']);
      map.getLayers().insertAt(0, layer['baseN']);
    }
    map.setView(new ol.View({
      zoom: defzoom,
      minZoom: 0,
      maxZoom: 12,
      extent: projObjectforCode[prj].extent,
      center: ol.proj.transform(projObjectforCode[prj].center, 'EPSG:4326', projObjectforCode[prj].projection['code_']),
      projection: projObjectforCode[prj].projection['code_']
    }))

    layer['baseN'].getSource().refresh();
    layer['baseS'].getSource().refresh();
    if (additional_layers) {
      layer['europaveg'].getSource().refresh();
      layer['fylkesveg'].getSource().refresh();
      layer['riksveg'].getSource().refresh();
    }
    build_draw(projObjectforCode[prj].projection['code_']);
    addExtraLayers(projObjectforCode[prj].projection['code_']);
  }
}

// Define all layers
var layer = {};

// Base layer WMS north
layer['baseN'] = new ol.layer.Tile({
  type: 'base',
  title: 'bgN',
  source: new ol.source.TileWMS({
    url: 'https://public-wms.met.no/backgroundmaps/northpole.map',
    params: {
      'LAYERS': 'world',
      'TRANSPARENT': 'false',
      'VERSION': '1.1.1',
      'FORMAT': 'image/png'
    },
    crossOrigin: 'anonymous'
  })
});

// Base layer WMS south
layer['baseS'] = new ol.layer.Tile({
  type: 'base',
  title: 'bgS',
  source: new ol.source.TileWMS({
    url: 'https://public-wms.met.no/backgroundmaps/southpole.map',
    params: {
      'LAYERS': 'world',
      'TRANSPARENT': 'false',
      'VERSION': '1.3.0',
      'FORMAT': 'image/png'
    },
    crossOrigin: 'anonymous'
  })
});

var map = new ol.Map({
  target: 'map-search',
  layers: [layer['baseN']
  ],
  view: new ol.View({
    zoom: defzoom,
    minZoom: 0,
    maxZoom: 12,
    center: ol.proj.transform([lon, lat], 'EPSG:4326', projObjectforCode[init_proj].projection['code_']),
    extent: projObjectforCode[init_proj].extent,
    projection: projObjectforCode[init_proj].projection['code_'],
  })
});

//Mouseposition
var mousePositionControl = new ol.control.MousePosition({
  coordinateFormat: function (co) {
    return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
  },
  projection: 'EPSG:4326',
});
map.addControl(mousePositionControl);

function build_draw(proj) {

  // Add drawing vector source
  var drawingSource = new ol.source.Vector({
    projection: proj
  });
  //Add drawing layer
  var drawingLayer = new ol.layer.Vector({
    source: drawingSource,
    title: 'draw',
    projection: proj
  });
  map.addLayer(drawingLayer);

  var geometryFunction = function (coordinates, geometry, projection) {
    var start = coordinates[0]; //x,y
    var end = coordinates[1];

    // transform in latlon
    var start_ll = ol.proj.transform(start, projection, 'EPSG:4326'); //lon,lat
    var end_ll = ol.proj.transform(end, projection, 'EPSG:4326');
    var left_ll = [start_ll[0], end_ll[1]];
    var right_ll = [end_ll[0], start_ll[1]];

    var left = ol.proj.transform(left_ll, 'EPSG:4326', projection);
    var right = ol.proj.transform(right_ll, 'EPSG:4326', projection);

    const boxCoordinates = [
      [
        start, left, end, right, start,
      ],
    ];

    if (geometry) {
      geometry.setCoordinates(boxCoordinates);
    }
    else {
      geometry = new ol.geom.Polygon(boxCoordinates);
    }
    return geometry;
  }
  var draw; // global so we can remove it later
  draw = new ol.interaction.Draw({
    source: drawingSource,
    type: 'LineString',
    //geometryFunction: ol.interaction.Draw.createBox(),
    geometryFunction: geometryFunction,
    maxPoints: 2
  });

  var tllat = drupalSettings.metsis_search.tllat;
  var tllon = drupalSettings.metsis_search.tllon;
  var brlat = drupalSettings.metsis_search.brlat;
  var brlon = drupalSettings.metsis_search.brlon;
  console.log(tllat);

// var tllat = document.getElementById('edit-bbox-top-left-lat');
// var tllon = document.getElementById('edit-bbox-top-left-lon');
// var brlat = document.getElementById('edit-bbox-bottom-right-lat');
// var brlon = document.getElementById('edit-bbox-bottom-right-lon');

  draw.on('drawstart', function (e) {
    drawingSource.clear();
  });

  draw.on('drawend', function (e) {

    coords = e.feature.getGeometry().getCoordinates();
    var a = ol.proj.transform(coords[0][0], map.getView().getProjection().getCode(), 'EPSG:4326');
    var b = ol.proj.transform(coords[0][1], map.getView().getProjection().getCode(), 'EPSG:4326');
    var c = ol.proj.transform(coords[0][2], map.getView().getProjection().getCode(), 'EPSG:4326');
    var d = ol.proj.transform(coords[0][3], map.getView().getProjection().getCode(), 'EPSG:4326');
    var e = ol.proj.transform(coords[0][4], map.getView().getProjection().getCode(), 'EPSG:4326');
    var topLeft = [Math.min(a[0], c[0]), Math.max(a[1], c[1])];
    var bottomRight = [Math.max(a[0], c[0]), Math.min(a[1], c[1])];

    if (topLeft[0] < -180) {
      topLeft[0] += 360;
    }
    else if (topLeft[0] > 180) {
      topLeft[0] -= 360;
    }
    if (bottomRight[0] < -180) {
      bottomRight[0] += 360;
    }
    else if (bottomRight[0] > 180) {
      bottomRight[0] -= 360;
    }
    if (topLeft[0] < 0 && bottomRight[0] > 0 && bottomRight[0] - topLeft[0] > 180) {
      var topLeftCopy = topLeft[0];
      topLeft[0] = bottomRight[0];
      bottomRight[0] = topLeftCopy;
    }

    jQuery(tllat).attr('value', topLeft[1]);
    jQuery(tllon).attr('value', topLeft[0]);
    jQuery(brlat).attr('value', bottomRight[1]);
    jQuery(brlon).attr('value', bottomRight[0]);

    var myurl = '/metsis/search/map?tllat=' + topLeft[1] + '&tllon=' + topLeft[0] + '&brlat=' + bottomRight[1] + '&brlon=' + bottomRight[0];
    $.ajax({
      url: myurl
    });

  });

  map.addInteraction(draw);

  // recreate drawings when fields are filled
  if (tllat.value !== '' && tllon.value !== '' && brlat !== '' && brlon !== '') {
    var topLeft = [Number(tllon.value), Number(tllat.value)];
    var bottomRight = [Number(brlon.value), Number(brlat.value)];
    if (bottomRight[0] < topLeft[0]) {
      topLeft[0] -= 360;
    }

    var points = [[
      ol.proj.transform(topLeft, 'EPSG:4326', proj),
      ol.proj.transform([bottomRight[0], topLeft[1]], 'EPSG:4326', proj),
      ol.proj.transform(bottomRight, 'EPSG:4326', proj),
      ol.proj.transform([topLeft[0], bottomRight[1]], 'EPSG:4326', proj),
    ]];

    var Square = new ol.geom.Polygon(points);
    var SquareFeature = new ol.Feature(Square);

    drawingSource.addFeature(SquareFeature);
    //Fit to extent of features
    if (ol.extent.containsExtent(projObjectforCode[proj].extent, map.getLayers().getArray()[1].getSource().getExtent())) {
      map.getView().fit(map.getLayers().getArray()[1].getSource().getExtent());
      map.getView().setZoom(map.getView().getZoom() - 1);
    }
  }

}

build_draw(init_proj);

function addExtraLayers(proj) {

  document.getElementById("droplayers").style.display = "none";

  if (additional_layers && (proj == 'EPSG:4326' || proj == 'EPSG:32661')) {
    $('#droplayers').appendTo(
      $('.ol-overlaycontainer-stopevent')
    );
    layer['europaveg'] = new ol.layer.Tile({
      title: 'europaveg',
      source: new ol.source.TileWMS({
        url: 'https://openwms.statkart.no/skwms1/wms.vegnett?',
        params: {
          'LAYERS': 'europaveg',
          'TRANSPARENT': 'true',
          'VERSION': '1.3.0',
          'FORMAT': 'image/png',
          'CRS': proj
        },
        crossOrigin: 'anonymous'
      })
    });

    layer['riksveg'] = new ol.layer.Tile({
      title: 'riksveg',
      displayInLayerSwitcher: TRUE,
      source: new ol.source.TileWMS({
        url: 'https://openwms.statkart.no/skwms1/wms.vegnett?',
        params: {
          'LAYERS': 'riksveg',
          'TRANSPARENT': 'true',
          'VERSION': '1.3.0',
          'FORMAT': 'image/png',
          'CRS': proj
        },
        crossOrigin: 'anonymous'
      })
    });

    layer['fylkesveg'] = new ol.layer.Tile({
      title: 'fylkesveg',
      source: new ol.source.TileWMS({
        url: 'https://openwms.statkart.no/skwms1/wms.vegnett?',
        params: {
          'LAYERS': 'fylkesveg',
          'TRANSPARENT': 'true',
          'VERSION': '1.3.0',
          'FORMAT': 'image/png',
          'CRS': proj
        },
        crossOrigin: 'anonymous'
      })
    });

    var ald = document.getElementById("lrslist").children; //list of li
    for (var i = ald.length; i--;) {
      if (ald[i].children[0].checked) {
        selectedLayer = ald[i].children[0].value;
        map.addLayer(layer[selectedLayer]);
      }
      ald[i].children[0].onclick = function select_extralayer() {
        if (this.checked) {
          selectedLayer = this.value;
          map.addLayer(layer[selectedLayer]);
        }
        else {
          selectedLayer = this.value;
          map.removeLayer(layer[selectedLayer]);
        }
      }
    }

    document.getElementById("droplayers").style.display = "inline";
  }
}

addExtraLayers(init_proj);
//});
console.log("End of searchmap.js script");
//});
//}
})
})(jQuery, Drupal, drupalSettings);
