// Import variables from php: array(address, id, layers)
var lon = Drupal.settings.lon;
var lat = Drupal.settings.lat;
var defzoom = Drupal.settings.zoom;
init_proj = 'EPSG:4326';

// 32661
proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
ol.proj.proj4.register(proj4);
var ext32661 = [-4e+06,-3e+06,8e+06,8e+06];
var center32661 = [15,70];
var proj32661 = new ol.proj.Projection({
  code: 'EPSG:32661',
  extent: ext32661
});

// 32761
proj4.defs('EPSG:32761', '+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +ellps=WGS84 +datum=WGS84 +units=m +no_defs');
ol.proj.proj4.register(proj4);
var ext32761 = [-8e+06,-8e+06,12e+06,10e+06];
var center32761 = [15,-90]; 
var proj32761 = new ol.proj.Projection({
  code: 'EPSG:32761',
  extent: ext32761
});


// 4326
var ext4326 = [-180.0000, -90.0000, 180.0000, 90.0000]; 
var center4326 = [15,70]; 
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

document.getElementById('4326').checked = true;

for (var i = ch.length; i--;) {
   ch[i].onchange = function change_projection() {
      var prj = this.value;
      if (prj == 'EPSG:32761') {
        map.getLayers().removeAt(0,layer['baseN']);
        map.getLayers().insertAt(0,layer['baseS']);
      }else{
        map.getLayers().removeAt(0,layer['baseS']);
        map.getLayers().insertAt(0,layer['baseN']);
      }
      map.setView(new ol.View({
                    zoom: defzoom,
                    minZoom: 0,
                    maxZoom: 12,
                    extent: projObjectforCode[prj].extent,
                    center: ol.proj.transform(projObjectforCode[prj].center, 'EPSG:4326', projObjectforCode[prj].projection),
                    projection: projObjectforCode[prj].projection,}))

      layer['baseN'].getSource().refresh();
      layer['baseS'].getSource().refresh();
      }
}

// Define all layers
var layer = {};

// Base layer WMS north
layer['baseN']  = new ol.layer.Tile({
   type: 'base',
   title: 'bgN',
   source: new ol.source.TileWMS({ 
       url: 'https://public-wms.met.no/backgroundmaps/northpole.map',
       params: {'LAYERS': 'world', 'TRANSPARENT':'false', 'VERSION':'1.1.1','FORMAT':'image/png'},
       crossOrigin: 'anonymous'
   })
});

// Base layer WMS south
layer['baseS']  = new ol.layer.Tile({
   type: 'base',
   title: 'bgS',
   source: new ol.source.TileWMS({ 
       url: 'https://public-wms.met.no/backgroundmaps/southpole.map',
       params: {'LAYERS': 'world', 'TRANSPARENT':'false', 'VERSION':'1.3.0','FORMAT':'image/png'},
       crossOrigin: 'anonymous'
   })
});

var map = new ol.Map({
   target: 'map-search',
   layers: [ layer['baseN']
           ],
   view: new ol.View({
                 zoom: defzoom, 
                 minZoom: 0,
                 maxZoom: 12,
                 center: ol.proj.transform([lon,lat], 'EPSG:4326', projObjectforCode[init_proj].projection),
                 extent: projObjectforCode[init_proj].extent,
                 projection: projObjectforCode[init_proj].projection,
   })
});

//Mouseposition
var mousePositionControl = new ol.control.MousePosition({
   coordinateFormat : function(co) {
      return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
   },
   projection : 'EPSG:4326', 
});
map.addControl(mousePositionControl);

// Add drawing vector source
var drawingSource = new ol.source.Vector({
    projection: 'EPSG:4326'
});
//Add drawing layer
var drawingLayer = new ol.layer.Vector({
    source: drawingSource,
    title: 'draw',
    projection: 'EPSG:4326',
});
map.addLayer(drawingLayer);

var draw; // global so we can remove it later
draw = new ol.interaction.Draw({
   source: drawingSource,
   type: 'Circle',
   geometryFunction: ol.interaction.Draw.createBox(),
   maxPoints: 2
});


var tllat = document.getElementById('edit-bbox-top-left-lat');
var tllon = document.getElementById('edit-bbox-top-left-lon');
var brlat = document.getElementById('edit-bbox-bottom-right-lat');
var brlon = document.getElementById('edit-bbox-bottom-right-lon');

draw.on('drawstart', function (e) {
   drawingSource.clear();
});

draw.on('drawend', function (e) {
 
   var extent = e.feature.getGeometry().getExtent();
   console.log(extent);
   if (extent && extent.length === 4) {
      extent = ol.proj.transformExtent(extent, map.getView().getProjection().getCode(), 'EPSG:4326');
      var topLeft = ol.extent.getTopLeft(extent);
      var bottomRight = ol.extent.getBottomRight(extent);
   }

   jQuery(tllat).attr('value', topLeft[1]);
   jQuery(tllon).attr('value', topLeft[0]);
   jQuery(brlat).attr('value', bottomRight[1]);
   jQuery(brlon).attr('value', bottomRight[0]);


    console.log('end');
});

map.addInteraction(draw);

// recreate drawings when fields are filled
if (tllat.value !== '' && tllon.value !== '' && brlat !== '' && brlon !== '') {
   var points = [[[tllon.value, tllat.value],
                [brlon.value, tllat.value],
                [brlon.value, brlat.value],
                [tllon.value, brlat.value],
   ]];

   var Square = new ol.geom.Polygon(points);
   var SquareFeature = new ol.Feature(Square);

   drawingSource.addFeature(SquareFeature);
//Fit to extent of features
   map.getView().fit(map.getLayers().getArray()[1].getSource().getExtent());
   map.getView().setZoom(map.getView().getZoom() - 1);

}

   map.on('change:view', function () {
       //outerThis.clear();
       jQuery(tllat).attr('value', '');
       jQuery(tllon).attr('value', '');
       jQuery(brlat).attr('value', '');
       jQuery(brlon).attr('value', '');
   });

