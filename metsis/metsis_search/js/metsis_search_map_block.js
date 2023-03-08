(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.metsisSearchBlock = {
    attach: function (context, drupalSettings) {
      $('#map-res', context).once('metsisSearchBlock').each(function () {
        console.log('inside jquery once');
        //initialize projection
        var defzoom = 2;

        // Import variables from drupalSettings send by block build array
        var extracted_info = drupalSettings.metsis_search_map_block.extracted_info;
        var path = drupalSettings.metsis_search_map_block.path;
        var pins = drupalSettings.metsis_search_map_block.pins;
        var site_name = drupalSettings.metsis_search_map_block.site_name;
        //var init_proj = drupalSettings.metsis_search_map_block.init_proj_res_map;

        var lat = drupalSettings.metsis_search_map_block.mapLat;
        var lon = drupalSettings.metsis_search_map_block.mapLon;
        var mapZoom = drupalSettings.metsis_search_map_block.mapZoom;
        var bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
        var mapFilter = drupalSettings.metsis_search_map_block.mapFilter;

        var init_proj = drupalSettings.metsis_search_map_block.init_proj;
        var projections = drupalSettings.metsis_search_map_block.projections;
        var layers_list = drupalSettings.metsis_search_map_block.layers_list;
        var additional_layers = drupalSettings.metsis_search_map_block.additional_layers;
        var tllat = drupalSettings.metsis_search_map_block.tllat;
        var tllon = drupalSettings.metsis_search_map_block.tllon;
        var brlat = drupalSettings.metsis_search_map_block.brlat;
        var brlon = drupalSettings.metsis_search_map_block.brlon;
        var base_layer_wms_north = drupalSettings.metsis_search_map_block.base_layer_wms_north;
        var base_layer_wms_south = drupalSettings.metsis_search_map_block.base_layer_wms_south;
        console.log('base layer north: ' + base_layer_wms_north);
        console.log('base layer south: ' + base_layer_wms_south);
        console.log('pins :' + pins);
        console.log('layers: ' + additional_layers);
        console.log('init proj: ' + init_proj);

        // Create the projections input boxes
        for (let key in projections) {
          var value = projections[key];
          $('.proj-wrapper').append(
            $(document.createElement('input')).prop({
              id: key,
              name: 'map-res-projection',
              value: key,
              type: 'radio',
              class: 'projections'
            })
          ).append(
            $(document.createElement('label')).prop({
              class: "proj-labels",
              for: key
            }).html(value)
          );
        }
        // Do some styling
        $('.proj-labels').css({
          "display": "inline-block",
          "font-weight": "normal",
          "padding-right": "10px",
          "vertical-align": "middle"
        });
        $('.projections').css({
          "padding-left": "10px",
          "padding-right": "0px",
          "vertical-align": "middle"
        });

        //If additional lyers are set, create the layers dropdown button list
        if (additional_layers) {
          console.log('Creating additonal layers dropdown  button');
          $('.layers-wrapper').append(
            $(document.createElement('div')).prop({
              id: 'droplayers',
              class: 'layers'
            }));
          $('#droplayers').append(
            $(document.createElement('button')).prop({
              class: 'layers-button',
              onclick: "document.getElementById('lrs').classList.toggle('show')",
            }).html('Layers'));
          $('#droplayers').append(
            $(document.createElement('div')).prop({
              id: "lrs",
              class: "panel dropdown-lrs-content",
            }));
          $('#lrs').append(
            $(document.createElement('ul')).prop({
              id: "lrslist"
            }));

          for (var key in layers_list) {
            var value = layers_list[key];
            console.log("Creating additional layer: " + value);
            $('#lrslist').append(
              $(document.createElement('li')).prop({
                class: 'addl'
              })
                .append(
                  $(document.createElement('input')).prop({
                    id: value,
                    class: 'check-layers',
                    type: 'checkbox',
                    value: value,
                    name: "layers"
                  }))
                .append(
                  $(document.createElement('label')).prop({
                    class: "layer-labels",
                    for: value
                  }).html(value))
            );
          }
          //Add event listener to layers button
          $(".layers-button").click(function () {
            document.getElementById('lrs').classList.toggle('show');
          });
          //Do some styling
          $('.layer-labels').css({
            "display": "inline-block",
            "font-weight": "normal",
            "padding-right": "10px",
            "vertical-align": "middle"
          });
          $('.check-layers').css({
            "vertical-align": "middle",
            "padding-right": "5px",
          });

        }

        //display current bbox search filter

        $('.current-bbox-filter').append('Current filter: ' + mapFilter);
        if (bboxFilter != NULL) {
          $('.current-bbox-select').append(bboxFilter);
        }

        // two projections will be possible
        // 32661
        proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
        ol.proj.proj4.register(proj4);
        var ext32661 = [-6e+06, -3e+06, 9e+06, 6e+06];
        var center32661 = [0, 80];
        var proj32661 = new ol.proj.Projection({
          code: 'EPSG:32661',
          extent: ext32661
        });

        // 32761
        proj4.defs('EPSG:32761', '+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +ellps=WGS84 +datum=WGS84 +units=m +no_defs');
        ol.proj.proj4.register(proj4);
        var ext32761 = [-8e+06, -8e+06, 12e+06, 10e+06];
        var center32761 = [0, -90];
        var proj32761 = new ol.proj.Projection({
          code: 'EPSG:32761',
          extent: ext32761
        });


        // 4326
        var ext4326 = [-350.0000, -100.0000, 350.0000, 100.0000];
        var center4326 = [15, 0];
        var proj4326 = new ol.proj.Projection({
          code: 'EPSG:4326',
          extent: ext4326
        });

        projObjectforCode = {
          'EPSG:4326': { extent: ext4326, center: center4326, projection: proj4326 },
          'EPSG:32661': { extent: ext32661, center: center32661, projection: proj32661 },
          'EPSG:32761': { extent: ext32761, center: center32761, projection: proj32761 }
        };

        var ch = document.getElementsByName('map-res-projection');

        document.getElementById(init_proj).checked = TRUE;
        var prj = init_proj;
        for (var i = ch.length; i--;) {
          ch[i].onchange = function change_projection() {
            prj = this.value;
            console.log("change projection event: " + prj);
            if (prj == 'EPSG:32761') {
              if (pins) {
                map.getLayers().removeAt(2, layer['pins']);
              }
              map.getLayers().removeAt(1, layer['polygons']);
              map.getLayers().removeAt(0, layer['baseN']);
              map.getLayers().insertAt(0, layer['baseS']);
            }
            else {
              console.log("change projection event: else statement");
              if (pins) {
                map.getLayers().removeAt(2, layer['pins']);
              }
              map.getLayers().removeAt(1, layer['polygons']);
              map.getLayers().removeAt(0, layer['baseS']);
              map.getLayers().insertAt(0, layer['baseN']);
              //alert(map.getLayers());
            }
            layer['baseN'].getSource().refresh({ force: TRUE });
            layer['baseS'].getSource().refresh({ force: TRUE });
            map.setView(new ol.View({
              zoom: defzoom,
              minZoom: 0,
              maxZoom: 12,
              extent: projObjectforCode[prj].extent,
              center: ol.proj.transform(projObjectforCode[prj].center, 'EPSG:4326', projObjectforCode[prj].projection),
              projection: projObjectforCode[prj].projection,
            }));

            layer['baseN'].getSource().refresh({ force: TRUE });
            layer['baseS'].getSource().refresh({ force: TRUE });

            //Adding try catch to aviod errors when layers are not defined
            try {
              if (additional_layers) {
                layer['europaveg'].getSource().refresh();
                layer['fylkesveg'].getSource().refresh();
                layer['riksveg'].getSource().refresh();
              }
            }
            catch (e) {
              console.log('additional layers already removed');
            }
            //clear pins and polygons
            //When in bbox mode this code catches Type Error.
            // we catch this exception and log some info instead
            try {
              if (map.getLayers().getArray().length !== 1) {
                map.getLayers().getArray()[1].getSource().clear(TRUE);
                if (pins) {
                  map.getLayers().getArray()[2].getSource().clear(TRUE);
                }
              }
            }
            catch (e) {
              console.log('layers already removed');
            }
            //rebuild vector source
            console.log("buildFeatures with proj " + prj);
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
            src: '/' + path + '/icons/pinBl.png'
          }))
        });

        var iconStyleGr = new ol.style.Style({
          image: new ol.style.Icon(({
            anchor: [0.5, 0.0],
            anchorOrigin: 'bottom-left',
            anchorXUnits: 'fraction',
            anchorYUnits: 'fraction',
            opacity: 1.00,
            src: '/' + path + '/icons/pinGr.png'
          }))
        });

        var iconStyleBk = new ol.style.Style({
          image: new ol.style.Icon(({
            anchor: [0.5, 0.0],
            anchorOrigin: 'bottom-left',
            anchorXUnits: 'fraction',
            anchorYUnits: 'fraction',
            opacity: 1.00,
            src: '/' + path + '/icons/pinBk.png'
          }))
        });

        // Define all layers
        var layer = {};

        // Base layer WMS north
        layer['baseN'] = new ol.layer.Tile({
          type: 'base',
          title: 'bgN',
          source: new ol.source.TileWMS({
            url: base_layer_wms_north,
            params: {
              'LAYERS': 'world',
              'TRANSPARENT': 'FALSE',
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
            url: base_layer_wms_south,
            params: {
              'LAYERS': 'world',
              'TRANSPARENT': 'FALSE',
              'VERSION': '1.3.0',
              'FORMAT': 'image/png'
            },
            crossOrigin: 'anonymous'
          })
        });
        var map_layer = layer['baseN'];
        if (init_proj == 'EPSG:32761') {
          map_layer = layer['baseS'];
        }
        if (init_proj == 'EPSG:32661') {
          map_layer = layer['baseN'];
        }

        console.log("Creating new map with layer: " + map_layer);
        var map = new ol.Map({
          target: 'map-res',
          layers: [map_layer],
          //layers: [layer['baseN']],
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
        tlphovMapRes.setAttribute("id", "tlphov-map-res")

        var overlayh = new ol.Overlay({
          element: tlphovMapRes,
        });
        map.addOverlay(overlayh);

        function id_tooltip_h() {
          map.on('pointermove', function (evt) {
            var coordinate = evt.coordinate;
            var feature_ids = {};
            map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
              //console.log(feature);
              feature_ids[feature.get('id')] = {
                title: feature.get('title'),
                id: feature.get('id')
              };
            });
            if (feature_ids.length !== 0) {
              tlphovMapRes.style.display = 'inline-block';
              tlphovMapRes.innerHTML = '';
              for (var id in feature_ids) {
                overlayh.setPosition(coordinate);
                overlayh.setPositioning('top-left');
                overlayh.setOffset([0, 20]);
                if (pins) {
                  tlphovMapRes.innerHTML += feature_ids[id].title + '<br>';
                }
                else {
                  tlphovMapRes.innerHTML += feature_ids[id].id + '<br>';
                }
              }
            }
            else {
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
        infoMapRes.setAttribute("id", "info-map-res");
        document.getElementById("map-res").appendChild(infoMapRes);
        infoMapRes.innerHTML = 'Interact directly with selected products from the map by clicking on the highlighted features. Select products from the table below to store them in your basket';

        // clickable ID in tooltop
        var tlpMapRes = document.createElement("div");
        tlpMapRes.setAttribute("id", "tlp-map-res");
        document.getElementById("map-res").appendChild(tlpMapRes);

        var toolclickevent;
        var toolclickevent_new;

        function tooltipclick_new(evt) {
          //overlay.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);
          $('.datasets-row').css('display', 'none');
          console.log('tooltiptipclick new event');
          var feature_ids = [];
          map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
            //feature_ids.push(feature.get('id'));
            //alert(feature.get('id'));
            //string = '.'+feature.get('id');
            //alert(string);
            id = feature.get('id');
            newId = id.replace(/_/g, "-");
            //alert(newId);
            $('.datasets-' + newId).css('display', 'block');
            //$('._'+newId).css('display', 'block');
            $(document).ready(function () {
              $('li.datasets-' + newId).focus();
            });
            // $(feature.get('id')).each(function() {
            //$(this).css('display', 'block');
            //});
          });

          //Reload the lazy loading of thumbnails
          var bLazy = new Blazy();
          bLazy.revalidate();
          //for (let i = 0; i < feature_ids.length; i++) {
          //alert(feature_ids[i]);
          //$('.'+feature_ids[i]).removeClass(['hidden']);
          //$('.'+feature_ids[i]).css('display', 'block');
          //}
          //$(feature_ids).css('display', 'block');
        }

        function tooltipclick(evt) {
          console.log('tooltiptipclick event');
          var coordinate = evt.coordinate;
          overlay.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);

          var feature_ids = {};

          map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {

            feature_ids[feature.get('id')] = {
              url_o: feature.get('url')[0],
              url_w: feature.get('url')[1],
              url_h: feature.get('url')[2],
              url_od: feature.get('url')[3],
              url_dln: feature.get('url')[4],
              url_dlo: feature.get('url')[5],
              id: feature.get('id'),
              extent: feature.get('extent'),
              latlon: feature.get('latlon'),
              title: feature.get('title'),
              abs: feature.get('abs'),
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
              project: feature.get('iso_keys_coll_act')[4],
            };
          });
          if (feature_ids.length !== 0) {
            tlpMapRes.style.display = 'inline-block';
            tlpMapRes.innerHTML = '';
            content.innerHTML = '';
            infoMapRes.innerHTML = '';
            for (var id in feature_ids) {
              var id_stp = id;
              id_stp = id.replace(/\s/g, '-');
              id_stp = id_stp.replace(/\./g, '-');
              id_stp = id_stp.replace(/\:/g, '-');
              id_stp = id_stp.replace(/\(/g, '-');
              id_stp = id_stp.replace(/\)/g, '-');
              var markup = `
    <table class="map-res-elements">
    <tr>
    <td style="width:60%;">${feature_ids[id].url_lp}</td>
    <td style="width=20%"><button type="button" class="adc-button" data-toggle="collapse" data-target="#md-more-${id_stp}">Additional Info</button></td>
    <td style="width=20%">${feature_ids[id].url_dln}</td>
    </tr>
    </table>
    <div id="md-more-${id_stp}" style="background-color:white; overflow-y: hidden; height: 0px" class="collapse">
    <table class="map-res-table-top">
      <tr>
      ${(feature_ids[id].thumb != '') ? '<td style="min-width:25%;">' + feature_ids[id].thumb + '</td>' : ''}<br>
      <td>
          <strong>Title: </strong>${feature_ids[id].title}<br>
          <strong>Abstract: </strong>${feature_ids[id].abs}<br>
          ${(feature_ids[id].institutions != ' ') ? '<strong>Institutions: </strong>' + feature_ids[id].institutions : ''}<br>
          ${(feature_ids[id].pi != '') ? '<strong>PI: </strong>' + feature_ids[id].pi : ''}<br>
          <table class="map-res-exp-buttons">
          <tr><td><button data-parent="#map-res-acc-${id_stp}" type="button" class="adc-button" data-toggle="collapse" style="margin-top: 2em;" data-target="#md-full-${id_stp}">Additional Metadata</button></td>
          <td><button data-parent="#map-res-acc-${id_stp}" type="button" class="adc-button" data-toggle="collapse" style="margin-top: 2em;" data-target="#md-access-${id_stp}">Data Access</button></td>
          <td>${feature_ids[id].url_dlo}</td>
          <td>${feature_ids[id].fimex}</td>
          <td><button data-parent="#map-res-acc-${id_stp}" type="button" class="adc-button"  style="display: ${(feature_ids[id].visualize_ts !== '') ? 'unset' : 'none'};" data-toggle="collapse" data-target=\"#md-ts-${id_stp}\" onclick="fetch_ts_variables('${feature_ids[id].url_o}', 'md-ts-${id_stp}');">Visualize</button></td>
          <td>${(feature_ids[id].visualize_thumb != ' ') ? '<a class="adc-button" href=' + feature_ids[id].thumb_url + '>Visualize</a>' : ''}</td>
          <td>${feature_ids[id].ascii_dl}</td>
          <td><a id="addtobasket-${id}" class="adc-button adc-sbutton use-ajax" href="/metsis/basket/add/${id}">Add to Basket</a></td>
          <td>${feature_ids[id].child}</td>
          </table>
      </td></tr>
    </table>
    <div id="map-res-acc-${id_stp}">
    <div class="panel map-res-panel">
    <div id="md-full-${id_stp}" style="background-color:white; overflow-y: hidden; height: 0px" class="collapse">
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
    <div id="md-access-${id_stp}" style="background-color:white; overflow-y: hidden; height: 0px" class="collapse">
    <table class="map-res-table">
      <tr><td style="width:15%;"><strong>HTTP access: </strong></td><td><a href="${feature_ids[id].url_h}">${feature_ids[id].url_h}</a></td></tr>
      <tr><td style="width:15%;"><strong>OPeNDAP access: </strong></td><td><a href="${feature_ids[id].url_o}.html">${feature_ids[id].url_o}</a></td></tr>
      <tr><td style="width:15%;"><strong>WMS access: </strong></td><td><a href="${feature_ids[id].url_w}?SERVICE=WMS&REQUEST=GetCapabilities">${feature_ids[id].url_w}</a></td></tr>
      <tr><td style="width:15%;"><strong>ODATA access: </strong></td><td><a href="${feature_ids[id].url_od}">${feature_ids[id].url_od}</a></td></tr>
    </table>
    </div>
    </div>

    <div class="panel map-res-panel">
    <div id="md-ts-${id_stp}" style="background-color:white; overflow-y: hidden; height: 0px" class="collapse">
    <select name="var_list" onchange="plot_ts('${feature_ids[id].url_o}','md-ts-${id_stp}', '${path}');">
         <option>Choose a variable</option>
    </select>
    <input type="hidden" id="axis" value="y_axis" />
    <div name="tsplot" id="tsplot-${id}"></div>

    </div>
    </div>

    </div>
    </div>

    </div>
    </div>

    `;

              if (feature_ids[id].thumb !== '') {
                if (pins) {
                  content.innerHTML += '<button type="button" class="adc-button" data-toggle="collapse" data-target="#md-more-' + id + '">Show info: ' + feature_ids[id].title + '</button>' + feature_ids[id].thumb + "<br>";
                }
                else {
                  content.innerHTML += ' <button type="button" class="adc-button" data-toggle="collapse" data-target="#md-more-' + id + '">Show info: ' + feature_ids[id].id + '</button>' + feature_ids[id].thumb + "<br>";
                }
              }
              tlpMapRes.innerHTML += markup;
            }
          }
        }

        function id_tooltip() {
          //var tooltip = document.getElementById('tlp-map-res');
          console.log('inside id_tooltip');

          map.on('click', tooltipclick);
        }

        function id_tooltip_new() {
          //var tooltip = document.getElementById('tlp-map-res');
          console.log('inside id_tooltip_new');

          map.on('click', tooltipclick_new);
        }

        //build up the point/polygon features
        function buildFeatures(prj) {

          var iconFeaturesPol = [];
          for (var i12 = 0; i12 <= extracted_info.length - 1; i12++) {
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
            var iconFeaturesPin = [];
            for (var i12 = 0; i12 <= extracted_info.length - 1; i12++) {
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
                related_info: [extracted_info[i12][8][0], extracted_info[i12][8][1]],
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
              }
              else {
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
          if (map.getLayers().getArray().length !== 1) {
            if (map.getLayers().getArray()[1].getSource().getExtent().length != 0) {
              if (pins) {
                if (ol.extent.containsExtent(map.getLayers().getArray()[1].getSource().getExtent(), map.getLayers().getArray()[2].getSource().getExtent())) {
                  var maxExt = map.getLayers().getArray()[1].getSource().getExtent();
                }
                else {
                  var maxExt = map.getLayers().getArray()[2].getSource().getExtent();
                }
                if (ol.extent.containsExtent(map.getView().getProjection().getExtent(), maxExt)) {
                  map.getView().fit(maxExt);
                  map.getView().setZoom(map.getView().getZoom() - 1);
                }
                else {
                  map.getView().fit(map.getView().calculateExtent());
                }
              }
              else {
                if (ol.extent.containsExtent(map.getView().getProjection().getExtent(), map.getLayers().getArray()[1].getSource().getExtent())) {
                  map.getView().fit(map.getLayers().getArray()[1].getSource().getExtent());
                  map.getView().setZoom(map.getView().getZoom() - 1);
                }
                else {
                  map.getView().fit(map.getView().calculateExtent());
                }
              }
            }
            else {
              map.getView().fit(map.getView().calculateExtent());
            }
          }

        }

        //initialize features
        buildFeatures(projObjectforCode[init_proj].projection);

        // display clickable ID in tooltip
        console.log('calling id_tooltip');
        //id_tooltip()
        id_tooltip_new()
        id_tooltip_h()
        if (additional_layers) {
          console.log("Adding additional layers");
          addExtraLayers(init_proj);
        }
        //addExtraLayers(init_proj)

        //Mouseposition
        var mousePositionControl = new ol.control.MousePosition({
          coordinateFormat: function (co) {
            return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
          },
          projection: 'EPSG:4326',
        });
        map.addControl(mousePositionControl);

        //Zoom to extent
        var zoomToExtentControl = new ol.control.ZoomToExtent({});
        map.addControl(zoomToExtentControl);

        //Loop over the extracted info, and check how many wms resources we have
        var wmsProducts = [];
        for (var i = 0; i < extracted_info.length; i++) {
          id = extracted_info[i][1];
          wms = extracted_info[i][0][1];
          if (wms != NULL && wms != "") {
            wmsProducts.push(id);
          }
        }

        // If we have wms datasets in map, show the visualise all button
        if (wmsProducts.length > 0) {
          datasets = wmsProducts.join(',');
          $('#vizAllButton').css('display', 'block');
          $('#vizAllButton').append(
            $(document.createElement('a')).prop({
              href: '/metsis/map/wms?dataset=' + datasets,
            }).text('Viusalise all WMS resources in Map'));
        }
        // Search bbox filter
        $('#bboxButton').click(function () {
          console.log('click for bbox filter: ' + prj);
          //        var tllat;
          //        var tllon;
          //        var brlat;
          //        var brlon;

          // clear pins and polygons
          try {
            map.getLayers().getArray()[2].getSource().clear(TRUE);

          } catch (e) {
            console.log('layers already removed');
          }
          try {
            map.getLayers().getArray()[1].getSource().clear(TRUE);
          } catch (e) {
            console.log('layers already removed');
          }
          //clear id_tooltip
          //map.un('click', function tooltipclick(evt) {});
          map.un('click', tooltipclick);
          map.un('click', tooltipclick_new);

          //remove overlay
          map.removeControl(mousePositionControl);
          map.removeOverlay(overlay);
          map.removeOverlay(overlayh);

          //Mouseposition
          var mousePositionControl = new ol.control.MousePosition({
            coordinateFormat: function (co) {
              return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
            },
            projection: 'EPSG:4326',
          });
          map.addControl(mousePositionControl);

          build_draw(prj)

        });

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

          /*  var tllat = drupalSettings.metsis_search_map_block.tllat;
            var tllon = drupalSettings.metsis_search_map_block.tllon;
            var brlat = drupalSettings.metsis_search_map_block.brlat;
            var brlon = drupalSettings.metsis_search_map_block.brlon;
            bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
            console.log(bboxFilter);*/
          var mapFilter = drupalSettings.metsis_search_map_block.mapFilter;

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
            //console.log(proj); + proj
            //jQuery(tllat).attr('value', topLeft[1]);
            //jQuery(tllon).attr('value', topLeft[0]);
            //jQuery(brlat).attr('value', bottomRight[1]);
            //jQuery(brlon).attr('value', bottomRight[0]);

            var myurl = '/metsis/search/map?tllat=' + topLeft[1] + '&tllon=' + topLeft[0] + '&brlat=' + bottomRight[1] + '&brlon=' + bottomRight[0] + '&proj=' + proj;
            console.log('calling controller url: ' + myurl);
            data = Drupal.ajax({
              url: myurl,
              async: FALSE
            }).execute();

            $(document).ajaxComplete(function (event, xhr, settings) {
              console.log('ajax complete:' + drupalSettings.metsis_search_map_block.bboxFilter);
              var bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
              $('.current-bbox-select').replaceWith(bboxFilter);
            });

            console.log('finished');
          });

          map.addInteraction(draw);
          /*
          tllat = drupalSettings.metsis_search_map_block.tllat;
          tllon = drupalSettings.metsis_search_map_block.tllon;
          brlat = drupalSettings.metsis_search_map_block.brlat;
          brlon = drupalSettings.metsis_search_map_block.brlon;
          */
          console.log('tllat before draw existing filter' + tllat);
          // recreate drawings when fields are filled
          if (tllat !== '' && tllon !== '' && brlat !== '' && brlon !== '') {
            var topLeft = [Number(tllon), Number(tllat)];
            var bottomRight = [Number(brlon), Number(brlat)];
            if (bottomRight[0] < topLeft[0]) {
              topLeft[0] -= 360;
            }

            var points = [
              [
                ol.proj.transform(topLeft, 'EPSG:4326', proj),
                ol.proj.transform([bottomRight[0], topLeft[1]], 'EPSG:4326', proj),
                ol.proj.transform(bottomRight, 'EPSG:4326', proj),
                ol.proj.transform([topLeft[0], bottomRight[1]], 'EPSG:4326', proj),
              ]
            ];

            var Square = new ol.geom.Polygon(points);
            var SquareFeature = new ol.Feature(Square);

            drawingSource.addFeature(SquareFeature);
            //Fit to extent of features
            if (ol.extent.containsExtent(projObjectforCode[proj].extent, map.getLayers().getArray()[1].getSource().getExtent())) {
              map.getView().fit(map.getLayers().getArray()[1].getSource().getExtent());
              map.getView().setZoom(map.getView().getZoom() - 1);
            }
          }
          var bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
          var mapFilter = drupalSettings.metsis_search_map_block.mapFilter;
          // two proj
        }

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
                  'TRANSPARENT': 'TRUE',
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
                  'TRANSPARENT': 'TRUE',
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
                  'TRANSPARENT': 'TRUE',
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
                } else {
                  selectedLayer = this.value;
                  map.removeLayer(layer[selectedLayer]);
                }
              }
            }

            document.getElementById("droplayers").style.display = "inline";
          }
        }

      });

    },
  };
})(jQuery, Drupal, drupalSettings);

function fetch_ts_variables(url_o, md_ts_id) {
  fetch('https://ncapi.adc-ncplot.met.no/ncplot/plot?get=param&resource_url=' + url_o)
    .then(response => response.json())
    .then(data => {
      //clear options
      document.getElementById("axis").value = Object.keys(data);
      var opts = document.getElementById(md_ts_id).children['var_list'];
      var length = opts.options.length;
      for (i = length - 1; i > 0; i--) {
        opts.options[i] = NULL;
      }
      for (const variable of data[Object.keys(data)]) {
        var el = document.createElement("option");
        el.textContent = variable;
        el.value = variable;
        document.getElementById(md_ts_id).children[0].appendChild(el);
      }
    });
}

function plot_ts(url_o, md_ts_id, path) {
  let loader = '<img class="ts-plot-loader" src="/' + path + '/icons/loader.gif">';
  document.getElementById(md_ts_id).children['tsplot'].innerHTML = loader;
  var variable = document.getElementById(md_ts_id).children['var_list'].value;
  fetch('https://ncapi.adc-ncplot.met.no/ncplot/plot?get=plot&resource_url=' + url_o + '&variable=' + variable + '&axis=' + document.getElementById("axis").value)
    .then(function (response) {
      return response.json();
    })
    .then(function (item) {
      item.target_id = document.getElementById(md_ts_id).children['tsplot'].id;
      Bokeh.embed.embed_item(item);
      document.getElementById(md_ts_id).children['tsplot'].innerHTML = '';
    })
}
