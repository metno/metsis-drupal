//initialize projection
var defzoom = 2;

// Import variables from php: array(address, id, layers)
var extracted_info = Drupal.settings.extracted_info;
var path = Drupal.settings.path;
var pins = Drupal.settings.pins;
var site_name = Drupal.settings.site_name;
var init_proj = Drupal.settings.init_proj_res_map;

// two projections will be possible
// 32661
proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
ol.proj.proj4.register(proj4);
var ext32661 = [-4e+06,-3e+06,8e+06,8e+06];
var center32661 = [0,80];
var proj32661 = new ol.proj.Projection({
  code: 'EPSG:32661',
  extent: ext32661
});

// 32761
proj4.defs('EPSG:32761', '+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +ellps=WGS84 +datum=WGS84 +units=m +no_defs');
ol.proj.proj4.register(proj4);
var ext32761 = [-8e+06,-8e+06,12e+06,10e+06];
var center32761 = [0,-90]; 
var proj32761 = new ol.proj.Projection({
  code: 'EPSG:32761',
  extent: ext32761
});


// 4326
var ext4326 = [-350.0000, -100.0000, 350.0000, 100.0000]; 
var center4326 = [15,0]; 
var proj4326 = new ol.proj.Projection({
  code: 'EPSG:4326',
  extent: ext4326
});

projObjectforCode = {
   'EPSG:4326': {extent: ext4326, center: center4326, projection: proj4326},
   'EPSG:32661': {extent: ext32661, center: center32661, projection: proj32661},
   'EPSG:32761': {extent: ext32761, center: center32761, projection: proj32761}
   };


var ch = document.getElementsByName('map-res-projection');

for (var i = ch.length; i--;) {
   ch[i].onchange = function change_projection() {
      var prj = this.value;
      if (prj == 'EPSG:32761') {
        if (pins) {
	   map.getLayers().removeAt(2,layer['pins']);
	}
        map.getLayers().removeAt(1,layer['polygons']);
        map.getLayers().removeAt(0,layer['baseN']);
        map.getLayers().insertAt(0,layer['baseS']);
      }else{
        if (pins) {
           map.getLayers().removeAt(2,layer['pins']);
	}
        map.getLayers().removeAt(1,layer['polygons']);
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
      //clear pins and polygons
      if(map.getLayers().getArray().length !== 1) {
         map.getLayers().getArray()[1].getSource().clear(true);
         if (pins) {
            map.getLayers().getArray()[2].getSource().clear(true);
         }
      }
      //rebuild vector source
      buildFeatures(projObjectforCode[prj].projection);
      }
}

//in nbs s1-ew
var featureStyleBl = new ol.style.Style({
    fill: new ol.style.Fill({
      color: 'rgba(0,0,255,0.1)',
    }),
    stroke: new ol.style.Stroke({
      color: 'blue',
      width: 2
    }),
});

var featureStyleGr = new ol.style.Style({
    fill: new ol.style.Fill({
      color: 'rgba(186, 168, 168,0.1)',
    }),
    stroke: new ol.style.Stroke({
      color: 'gray',
      width: 2
    }),
});

var iconStyleBl = new ol.style.Style({
    image: new ol.style.Icon(({
      anchor: [0.5, 0.0],
      anchorOrigin: 'bottom-left',
      anchorXUnits: 'fraction',
      anchorYUnits: 'fraction',
      opacity: 1.00,
      src: '/'+path+'/icons/pinBl.png'
   }))
});

var iconStyleGr = new ol.style.Style({
    image: new ol.style.Icon(({
      anchor: [0.5, 0.0],
      anchorOrigin: 'bottom-left',
      anchorXUnits: 'fraction',
      anchorYUnits: 'fraction',
      opacity: 1.00,
      src: '/'+path+'/icons/pinGr.png'
   }))
});

var iconStyleBk = new ol.style.Style({
    image: new ol.style.Icon(({
      anchor: [0.5, 0.0],
      anchorOrigin: 'bottom-left',
      anchorXUnits: 'fraction',
      anchorYUnits: 'fraction',
      opacity: 1.00,
      src: '/'+path+'/icons/pinBk.png'
   }))
});

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
   target: 'map-res',
   layers: [ layer['baseN']
           ],
   view: new ol.View({
                 zoom: defzoom, 
                 minZoom: 0,
                 maxZoom: 12,
                 center: projObjectforCode[init_proj].center,
                 extent: projObjectforCode[init_proj].extent,
                 projection: projObjectforCode[init_proj].projection,
   })
});

// title on hover tooltip
var tlphovMapRes = document.createElement("div");
tlphovMapRes.setAttribute("id","tlphov-map-res")

var overlayh = new ol.Overlay({
  element: tlphovMapRes,
});
map.addOverlay(overlayh);

function id_tooltip_h(){
  map.on('pointermove', function(evt) {
    var coordinate = evt.coordinate;
    var feature_ids = {};
    map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
      //console.log(feature);
      feature_ids[feature.get('id')] = {title: feature.get('title'),
                                        id: feature.get('id')};
    });
    if(feature_ids.length !== 0) {
      tlphovMapRes.style.display = 'inline-block';
      tlphovMapRes.innerHTML = '';
      for(var id in feature_ids){
        overlayh.setPosition(coordinate);
        overlayh.setPositioning('top-left');
        overlayh.setOffset([0,20]);
        if (pins) {
           tlphovMapRes.innerHTML += feature_ids[id].title+'<br>';
        }else{
           tlphovMapRes.innerHTML += feature_ids[id].id+'<br>';
        }
      }
    }else{
      tlphovMapRes.style.display = 'hidden';
    }
  });
}

// add popup with thumbnails
var container = document.getElementById('popup-map-res');
var content = document.getElementById('popup-map-res-content');

var overlay = new ol.Overlay({
  element: container,
  className: 'map-res-thumb-pop'
});
map.addOverlay(overlay);

// clickable ID in tooltop
var infoMapRes = document.createElement("div");
infoMapRes.setAttribute("id","info-map-res")
document.getElementById("map-res").appendChild(infoMapRes);
infoMapRes.innerHTML = 'Interact directly with selected products from the map by clicking on the highlighted features. Select products from the table below to store them in your basket';

// clickable ID in tooltop
var tlpMapRes = document.createElement("div");
tlpMapRes.setAttribute("id","tlp-map-res")
document.getElementById("map-res").appendChild(tlpMapRes);

function id_tooltip(){
  //var tooltip = document.getElementById('tlp-map-res');

  map.on('click', function(evt) {

  var coordinate = evt.coordinate;
  overlay.setPosition([coordinate[0] + coordinate[0]*20/100, coordinate[1] +  coordinate[1]*20/100]);


  var feature_ids = {};

  map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
      feature_ids[feature.get('id')] = {url_o: feature.get('url')[0],
                                        url_w: feature.get('url')[1],
                                        url_h: feature.get('url')[2],
                                        url_od: feature.get('url')[3], 
                                        url_dln: feature.get('url')[4], 
                                        url_dlo: feature.get('url')[5], 
                                        id: feature.get('id'), 
                                        extent: feature.get('extent'), 
                                        latlon: feature.get('latlon'), 
                                        title: feature.get('title'), 
                                        abs:feature.get('abs'), 
                                        timeStart: feature.get('time')[0],
                                        timeEnd: feature.get('time')[1],
                                        thumb: feature.get('thumb')[0],
                                        thumb_url: feature.get('thumb')[1],
                                        url_lp: feature.get('related_info')[0],
                                        url_lp_url: feature.get('related_info')[1],
                                        ds_prod_status: feature.get('info_status')[0],
                                        md_status: feature.get('info_status')[1],
                                        last_md_update: feature.get('info_status')[2],
                                        dc_sh: feature.get('data_center')[0],
                                        dc_ln: feature.get('data_center')[1],
                                        dc_url: feature.get('data_center')[2],
                                        dc_cr: feature.get('data_center')[3],
                                        dc_cn: feature.get('data_center')[4],
                                        dc_ce: feature.get('data_center')[5],
                                        fimex: feature.get('actions')[0],
                                        visualize_ts: feature.get('actions')[1],
                                        ascii_dl: feature.get('actions')[2],
                                        child: feature.get('actions')[3],
                                        visualize_thumb: feature.get('actions')[4],
                                        institutions: feature.get('contacts')[0],
                                        pi: feature.get('contacts')[1],
                                        core: feature.get('core'),
                                        access_constraint: feature.get('constraints')[0],
                                        use_constraint: feature.get('constraints')[1],
                                        isotopic: feature.get('iso_keys_coll_act')[0],
                                        keywords: feature.get('iso_keys_coll_act')[1],
                                        collection: feature.get('iso_keys_coll_act')[2],
                                        activity: feature.get('iso_keys_coll_act')[3],
                                        project: feature.get('iso_keys_coll_act')[4],};
  });
  if(feature_ids.length !== 0) {
     tlpMapRes.style.display = 'inline-block';
     tlpMapRes.innerHTML = '';
     content.innerHTML = '';
     infoMapRes.innerHTML = '';
     for(var id in feature_ids){
var markup = `
<table class="map-res-elements">
<tr>
<td style="width:60%;">${feature_ids[id].url_lp}</td>
<td style="width=20%"><button type="button" class="adc-button" data-toggle="collapse" data-target="#md-more-${id}">Additional Info</button></td>
<td style="width=20%">${feature_ids[id].url_dln}</td>
</tr>
</table>

<div id="md-more-${id}" style="background-color:white; overflow-y: hidden; height: 0px" class="collapse">
<table class="map-res-table-top">
  <tr>
  ${(feature_ids[id].thumb != '') ? '<td style="min-width:25%;">'+feature_ids[id].thumb+'</td>' : ''}<br>
  <td>
      <strong>Title: </strong>${feature_ids[id].title}<br>
      <strong>Abstract: </strong>${feature_ids[id].abs}<br>
      ${(feature_ids[id].institutions != ' ') ? '<strong>Institutions: </strong>'+feature_ids[id].institutions : ''}<br>
      ${(feature_ids[id].pi != '') ? '<strong>PI: </strong>'+feature_ids[id].pi : ''}<br>
      <table class="map-res-exp-buttons">
      <tr><td><button data-parent="#map-res-acc-${id}" type="button" class="adc-button" data-toggle="collapse" style="margin-top: 2em;" data-target="#md-full-${id}">Additional Metadata</button></td>
      <td><button data-parent="#map-res-acc-${id}" type="button" class="adc-button" data-toggle="collapse" style="margin-top: 2em;" data-target="#md-access-${id}">Data Access</button></td> 
      <td>${feature_ids[id].url_dlo}</td>
      <td>${feature_ids[id].fimex}</td>
      <td>${feature_ids[id].visualize_ts}</td>
      <td>${(feature_ids[id].visualize_thumb != ' ') ? '<a class="adc-button" href='+feature_ids[id].thumb_url+'>Visualize</a>' : ''}</td>
      <td>${feature_ids[id].ascii_dl}</td>
      <td>${feature_ids[id].child}</td>
      </table>
  </td></tr>
</table>

<div id="map-res-acc-${id}">
<div class="panel map-res-panel">
<div id="md-full-${id}" style="background-color:white; overflow-y: hidden; height: 0px" class="collapse">
<table class="map-res-table">
  <tr><td colspan="2" style="width:25%;"><strong>Metadata ID: </strong></td><td>${feature_ids[id].id}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Last metadata update: </strong></td><td>${feature_ids[id].last_md_update}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Landing Page: </strong></td><td><a href="${feature_ids[id].url_lp_url}">${feature_ids[id].url_lp_url}</a></td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Time start: </strong></td><td>${feature_ids[id].timeStart}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Time end: </strong></td><td>${feature_ids[id].timeEnd}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Geographical extent (N,S,E,W): </strong></td><td>${feature_ids[id].extent}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Access constraint: </strong></td><td>${feature_ids[id].access_constraint}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Use constraint: </strong></td><td>${feature_ids[id].use_constraint}</td></tr>
  <tr><td style="width:10%;" rowspan="4"><strong>Data Access</strong></td><td>HTTP access: </td><td><a href="${feature_ids[id].url_h}">${feature_ids[id].url_h}</a></td></tr>
  <tr><td>OPeNDAP access: </td><td><a href="${feature_ids[id].url_o}.html">${feature_ids[id].url_o}</a></td></tr>
  <tr><td>WMS access: </td><td><a href="${feature_ids[id].url_w}?SERVICE=WMS&REQUEST=GetCapabilities">${feature_ids[id].url_w}</a></td></tr>
  <tr><td>ODATA access: </td><td><a href="${feature_ids[id].url_od}">${feature_ids[id].url_od}</a></td></tr>
  <tr><td style="width:10%;" rowspan="6"><strong>Data Center</strong></td><td>Short name: </td><td>${feature_ids[id].dc_sh}</td></tr>
  <tr><td>Long name: </td><td>${feature_ids[id].dc_ln}</td></tr>
  <tr><td>URL: </td><td><a href="${feature_ids[id].dc_url}">${feature_ids[id].dc_url}</a></td></tr>
  <tr><td>Contact role: </td><td>${feature_ids[id].dc_cr}</td></tr>
  <tr><td>Contact name: </td><td>${feature_ids[id].dc_cn}</td></tr>
  <tr><td>Contact email:</td><td>${feature_ids[id].dc_ce}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Isotopic Category: </strong></td><td>${feature_ids[id].isotopic}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Keywords: </strong></td><td>${feature_ids[id].keywords}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Collection: </strong></td><td>${feature_ids[id].collection}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Activity Type: </strong></td><td>${feature_ids[id].activity}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Project: </strong></td><td>${feature_ids[id].project}</td></tr>
  <tr><td colspan="2" style="width:25%;"><strong>Dataset production status: </strong></td><td>${feature_ids[id].ds_prod_status}</td></tr>
</table>
</div>
</div>

<div class="panel map-res-panel">
<div id="md-access-${id}" style="background-color:white; overflow-y: hidden; height: 0px" class="collapse">
<table class="map-res-table">
  <tr><td style="width:15%;"><strong>HTTP access: </strong></td><td><a href="${feature_ids[id].url_h}">${feature_ids[id].url_h}</a></td></tr>
  <tr><td style="width:15%;"><strong>OPeNDAP access: </strong></td><td><a href="${feature_ids[id].url_o}.html">${feature_ids[id].url_o}</a></td></tr>
  <tr><td style="width:15%;"><strong>WMS access: </strong></td><td><a href="${feature_ids[id].url_w}?SERVICE=WMS&REQUEST=GetCapabilities">${feature_ids[id].url_w}</a></td></tr>
  <tr><td style="width:15%;"><strong>ODATA access: </strong></td><td><a href="${feature_ids[id].url_od}">${feature_ids[id].url_od}</a></td></tr>
</table>
</div>
</div>

</div>
</div>

</div>
`;

   if(feature_ids[id].thumb !==''){
      if(pins){ 
         content.innerHTML += feature_ids[id].title+"<br>"+feature_ids[id].thumb+"<br>";
      }else{
         content.innerHTML += feature_ids[id].id+"<br>"+feature_ids[id].thumb+"<br>";
      }
   } 
   tlpMapRes.innerHTML += markup;  
}}});

}

//build up the point/polygon features
function buildFeatures(prj) {

var iconFeaturesPol=[];
for(var i12=0; i12 <= extracted_info.length-1; i12++){
if ((extracted_info[i12][2][0] !== extracted_info[i12][2][1]) || (extracted_info[i12][2][2] !== extracted_info[i12][2][3])) {
  box_tl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][0]], 'EPSG:4326', prj);
  box_tr = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][0]], 'EPSG:4326', prj);
  box_bl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][1]], 'EPSG:4326', prj);
  box_br = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][1]], 'EPSG:4326', prj);
  geom = new ol.geom.Polygon([[box_tl, box_tr, box_br, box_bl, box_tl]]);
  var iconFeaturePol = new ol.Feature({
	url: extracted_info[i12][0],
        id: extracted_info[i12][1],
        geometry: geom,
        extent: [extracted_info[i12][2][0], extracted_info[i12][2][1], extracted_info[i12][2][2], extracted_info[i12][2][3]],
        latlon: extracted_info[i12][3],
        title: extracted_info[i12][4],
        abs: extracted_info[i12][5],
        time: [extracted_info[i12][6][0], extracted_info[i12][6][1]],
        thumb: extracted_info[i12][7],
        related_info: extracted_info[i12][8],
        iso_keys_coll_act: extracted_info[i12][9],
        info_status: extracted_info[i12][10],
        data_center: extracted_info[i12][11],
        actions: extracted_info[i12][12],
        contacts: extracted_info[i12][13],
        constraints: extracted_info[i12][14],
        core: extracted_info[i12][15],
  });
  iconFeaturesPol.push(iconFeaturePol);

  iconFeaturePol.setStyle(featureStyleBl);
}
}

//create a vector source with all points
var vectorSourcePol = new ol.source.Vector({
  features: iconFeaturesPol
});

//create a vector layer with all points from the vector source and pins
var vectorLayerPol = new ol.layer.Vector({
   title: 'polygons',
   source: vectorSourcePol,
});
map.addLayer(vectorLayerPol);

//all points
if (pins) {
  var iconFeaturesPin=[];
  for(var i12=0; i12 <= extracted_info.length-1; i12++){
    geom = new ol.geom.Point(ol.proj.transform([extracted_info[i12][3][1], extracted_info[i12][3][0]], 'EPSG:4326', prj));
    var iconFeaturePin = new ol.Feature({
	url: extracted_info[i12][0],
        id: extracted_info[i12][1],
        geometry: geom,
        extent: [extracted_info[i12][2][0], extracted_info[i12][2][1], extracted_info[i12][2][2], extracted_info[i12][2][3]],
        latlon: extracted_info[i12][3],
        title: extracted_info[i12][4],
        abs: extracted_info[i12][5],
        time: [extracted_info[i12][6][0], extracted_info[i12][6][1]],
        thumb: extracted_info[i12][7],
        related_info: [extracted_info[i12][8][0],extracted_info[i12][8][1]],
        iso_keys_coll_act: extracted_info[i12][9],
        info_status: extracted_info[i12][10],
        data_center: extracted_info[i12][11],
        actions: extracted_info[i12][12],
        contacts: extracted_info[i12][13],
        constraints: extracted_info[i12][14],
        core: extracted_info[i12][15],
    });

    iconFeaturesPin.push(iconFeaturePin);
    if ((extracted_info[i12][2][0] !== extracted_info[i12][2][1]) || (extracted_info[i12][2][2] !== extracted_info[i12][2][3])) {
       iconFeaturePin.setStyle(iconStyleBl);
    }else{
       iconFeaturePin.setStyle(iconStyleBk);
    }
  }

//create a vector source with all points
  var vectorSourcePin = new ol.source.Vector({
    features: iconFeaturesPin
  });

//create a vector layer with all points from the vector source and pins
  var vectorLayerPin = new ol.layer.Vector({
    title: 'pins',
    source: vectorSourcePin,
    //style: iconStyle,
  });
 map.addLayer(vectorLayerPin);
}

//Fit to extent of features
//check if there are results
//if(map.getLayers().getArray().length !== 1) {
//   if (map.getLayers().getArray()[1].getSource().getFeatures().length != 0) {
//      if (ol.extent.containsExtent(map.getView().calculateExtent(), map.getLayers().getArray()[2].getSource().getExtent())) {
//         map.getView().fit(map.getLayers().getArray()[2].getSource().getExtent());
//      }else{
//         map.getView().fit(map.getView().calculateExtent());
//      }
//   }else{
//         map.getView().fit(map.getView().calculateExtent());
//   }
//}

}

//initialize features
buildFeatures(projObjectforCode[init_proj].projection);

// display clickable ID in tooltip
id_tooltip()
id_tooltip_h()

//Mouseposition
var mousePositionControl = new ol.control.MousePosition({
   coordinateFormat : function(co) {
      return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
   },
   projection : 'EPSG:4326', 
});
map.addControl(mousePositionControl);

//Zoom to extent
var zoomToExtentControl = new ol.control.ZoomToExtent({
});
map.addControl(zoomToExtentControl);
