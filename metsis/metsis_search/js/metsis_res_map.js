console.log("Start of metsis search map script:");
(function ($, Drupal, drupalSettings, once) {

  console.log("Attaching map script to drupal behaviours:");
  /** Attach the metsis map to drupal behaviours function */
  Drupal.behaviors.metsisSearchBlock = {
    attach: function (context) {

      const mapEl = $(once('#map-res', '[data-map-res]', context));
      //console.log(mapEl);
      mapEl.each(function () {
        //$('#map-res', context).once('metsisSearchBlock').each(function() {
        /** Start reading drupalSettings sent from the mapblock build */
        console.log('Initializing METSIS Map...');

        //Default Zoom value
        const defzoom = 4;

        // Import letiables from drupalSettings send by block build array
        var extracted_info = drupalSettings.metsis_search_map_block.extracted_info;
        var path = drupalSettings.metsis_search_map_block.path;
        var pins = drupalSettings.metsis_search_map_block.pins;
        const site_name = drupalSettings.metsis_search_map_block.site_name;

        const lat = drupalSettings.metsis_search_map_block.mapLat;
        const lon = drupalSettings.metsis_search_map_block.mapLon;
        const mapZoom = drupalSettings.metsis_search_map_block.mapZoom;
        const bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
        const mapFilter = drupalSettings.metsis_search_map_block.mapFilter;

        const init_proj = drupalSettings.metsis_search_map_block.init_proj;
        const projections = drupalSettings.metsis_search_map_block.projections;
        const layers_list = drupalSettings.metsis_search_map_block.layers_list;
        const additional_layers = drupalSettings.metsis_search_map_block.additional_layers;
        const tllat = drupalSettings.metsis_search_map_block.tllat;
        const tllon = drupalSettings.metsis_search_map_block.tllon;
        const brlat = drupalSettings.metsis_search_map_block.brlat;
        const brlon = drupalSettings.metsis_search_map_block.brlon;
        let selected_proj = drupalSettings.metsis_search_map_block.proj;
        let selected_filter = drupalSettings.metsis_search_map_block.cond;
        const base_layer_wms_north = drupalSettings.metsis_search_map_block.base_layer_wms_north;
        const base_layer_wms_south = drupalSettings.metsis_search_map_block.base_layer_wms_south;
        const pywpsUrl = drupalSettings.metsis_search_map_block.pywps_service;
        const current_search = drupalSettings.metsis_search_map_block.current_search;
        const wms_layers_skip = drupalSettings.metsis_search_map_block.wms_layers_skip;
        const bbox_filter = drupalSettings.metsis_search_map_block.bbox_filter;
        const bbox_operator = drupalSettings.metsis_search_map_block.bbox_op;
        const bbox_filter_auto_show = drupalSettings.bbox_filter_auto_show;
        const search_view = drupalSettings.metsis_search.search_view;

        // Some debugging
        const debug = true;
        if (debug) {
          console.log("Reading drupalSettings: ")
          console.log('base layer north: ' + base_layer_wms_north);
          console.log('base layer south: ' + base_layer_wms_south);
          console.log('show pins :' + pins);
          console.log('show additional layers: ' + additional_layers);
          console.log('init proj: ' + init_proj);
          console.log('current selected  projection: ' + selected_proj);
          console.log('current bbox: ' + brlat + ',' + brlon + ',' + tllat + ',' + tllon);
          console.log('init map_filter: ' + mapFilter);
          console.log('current selected map_filter: ' + selected_filter);
          console.log('current bbox_filter: ' + bboxFilter);
          console.log('initial map zoom: ' + mapZoom);
          console.log("WMS Layers to skip: ");
          console.log(wms_layers_skip);
          console.log("Extracted info: ");
          console.log(extracted_info);
          console.log(bbox_filter);
          console.log(bbox_operator);
          console.log(search_view);

        }

        //Make extracted info empty array if null or undefined
        if (extracted_info === null || extracted_info === undefined) {
          console.log('The array is null or undefined');
          extracted_info = [];
        }
        //Set the configured zoom level as the same as default:
        defZoom = mapZoom;
        //Set current selected projection to initial projection if not altered by user $session
        var proj = init_proj;
        if (selected_proj == null) {
          selected_proj = init_proj;
          proj = init_proj;
        } else {
          proj = selected_proj;
        }

        //Set the current selected filter
        if (selected_filter == null) {
          selected_filter = localStorage.getItem('map_bbox_op');
          if (selected_filter == null) {
            selected_filter = mapFilter;
          }

        }
        // Create the  map baselayer input boxses
        /*        $('.basemap-wrapper').append(
                  $(document.createElement('input')).prop({
                    key: 'OSMStandard',
                    name: 'baseLayerRadioButton',
                    value: 'OSMStandard',
                    type: 'radio',
                    checked: true
                  }) //.attr("checked", "")
                ).append(
                  $(document.createElement('label')).prop({
                    class: "basemap-labels",
                    for: 'OSMStandard'
                  }).html('OSMStandard')
                );
                $('.basemap-wrapper').append(
                  $(document.createElement('input')).prop({
                    key: 'OSMHumanitarian',
                    name: 'baseLayerRadioButton',
                    value: 'OSMHumanitarian',
                    type: 'radio',
                  })
                ).append(
                  $(document.createElement('label')).prop({
                    class: "basemap-labels",
                    for: 'OSMHumanitarian'
                  }).html('OSMHumanitarian')
                );

                $('.basemap-wrapper').append(
                  $(document.createElement('input')).prop({
                    key: 'stamenTerrain',
                    name: 'baseLayerRadioButton',
                    value: 'stamenTerrain',
                    type: 'radio',
                  })
                ).append(
                  $(document.createElement('label')).prop({
                    class: "basemap-labels",
                    for: 'stamenTerrain'
                  }).html('StamenTerrain')
                );
                $('.basemap-wrapper').append(
                  $(document.createElement('input')).prop({
                    key: 'ESRI',
                    name: 'baseLayerRadioButton',
                    value: 'ESRI',
                    type: 'radio',
                  })
                ).append(
                  $(document.createElement('label')).prop({
                    class: "basemap-labels",
                    for: 'ESRI'
                  }).html('ESRI Satellite')
                );

                // Do some styling
                $('.basemap-labels').css({
                  "display": "inline-block",
                  "font-weight": "normal",
                  //"padding-left": "0px",
                  "padding-right": "10px",
                  "vertical-align": "middle"
                });
                $('.basemap-wrapper').css({
                  "padding-left": "0px",
                  "padding-right": "0px",
                  "vertical-align": "middle"
                });
        */
        // Create the projections input boxes
        for (let key in projections) {
          let value = projections[key];
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

        //Create change filter radio buttons
        $('.map-filter-wrapper').append(
          $(document.createElement('input')).prop({
            id: 'within',
            name: 'map-filter',
            value: 'Within',
            type: 'radio',
            class: 'mapFilter'
          })
        ).append(
          $(document.createElement('label')).prop({
            class: "map-filter-labels",
            for: 'within'
          }).html('Within')
        );
        $('.map-filter-wrapper').append(
          $(document.createElement('input')).prop({
            id: 'intersects',
            name: 'map-filter',
            value: 'Intersects',
            type: 'radio',
            class: 'mapFilter'
          })
        ).append(
          $(document.createElement('label')).prop({
            class: "map-filter-labels",
            for: 'intersects'
          }).html('Intersects')
        );

        $('.map-filter-wrapper').on('change', 'input[type=radio][name=map-filter]', function () {
          changed_filter = this.value.toLowerCase();
          console.log(changed_filter);
          $('select[name="bbox_op"][data-drupal-selector="edit-bbox-op"]').val(changed_filter).prop('selected', true);
        });
        //Set default checked filter
        //let flt = document.getElementsByName('map-filter');
        if (bbox_operator != null) {
          selected_filter = bbox_operator.toLowerCase();
          console.log('selected filter: ' + selected_filter.toLowerCase());
          if (selected_filter !== 'contains') {
            document.getElementById(selected_filter.toLowerCase()).checked = true;
          }
        }
        else {
          selected_filter = localStorage.getItem('map_bbox_op');
          if (selected_filter === null) {
            selected_filter = 'intersects';
          }
          console.log('selected filter: ' + selected_filter.toLowerCase());
          if (selected_filter !== 'contains') {
            document.getElementById(selected_filter.toLowerCase()).checked = true;
          }
        }
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

          for (let key in layers_list) {
            let value = layers_list[key];
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

        if (bbox_filter != null && bbox_operator != null) {
          console.log("Setting active BBOX filter with remove X");
          const mapFilterInfo = selected_filter.charAt(0).toUpperCase() + selected_filter.slice(1)
          $('.current-bbox-filter-label').html('<strong>Active spatial filter:</strong> ');
          $('.current-bbox-filter').text(mapFilterInfo + ' ');
          $('.current-bbox-select').text('ENVELOPE((' + bbox_filter[0] + ',' + bbox_filter[1] + ',' + bbox_filter[2] + ',' + bbox_filter[3] + ')');
          $('.remove-bbox-filter').append(' <i id="remove-bbox-filter" class="fa fa-remove" style="color:red; cursor:pointer;" title="Remove geographic boundingbox filter"></i>');
          $('.remove-bbox-filter').click(function () {
            $('input[name="bbox[minX]"][data-drupal-selector="edit-bbox-minx"]').val('');
            $('input[name="bbox[maxX]"][data-drupal-selector="edit-bbox-maxx"]').val('');
            $('input[name="bbox[maxY]"][data-drupal-selector="edit-bbox-maxy"]').val('');
            $('input[name="bbox[minY]"][data-drupal-selector="edit-bbox-miny"]').val('');

            // Update the operator option.
            $('select[name="bbox_op"][data-drupal-selector="edit-bbox-op"]').val(selected_filter.toLowerCase()).prop('selected', true);;
            if (search_view === 'metsis_search') {
              $('#views-exposed-form-metsis-search-results').submit();
            }
            if (search_view === 'metsis_simple_search') {
              $('#views-exposed-form-metsis-simple-search-results').submit();
            }
          });

        }

        //Reset search button
        /*    $('#resetButton').on('click', function() {
              var myurl = '/metsis/search/reset';
              console.log('calling controller url: ' + myurl);
              data = Drupal.ajax({
                url: myurl,
                async: false,
                success: function(response) {
                     location.href = '/metsis/search?op=Reset'; //Redirect
                }
              }).execute();
            })
            */
        /**
         * Define the proj4 map_projections
         */
        //console.log(proj4);
        // two projections will be possible
        // 32661
        //proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
        proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +ellps=WGS84 +datum=WGS84 +units=m +no_defs');
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

        var projObjectforCode = {
          'EPSG:4326': {
            extent: ext4326,
            center: center4326,
            projection: proj4326
          },
          'EPSG:32661': {
            extent: ext32661,
            center: center32661,
            projection: proj32661
          },
          'EPSG:32761': {
            extent: ext32761,
            center: center32761,
            projection: proj32761
          }
        };

        /** Register event listener for baseMap selection */
        /*        const baseLayerElements = document.querySelectorAll(' .basemap-wrapper > input[type=radio]');
                for (let baseLayerElement of baseLayerElements) {
                  baseLayerElement.addEventListener('change', function() {
                    let baseLayerValue = this.value;
                    console.log("Changing baselayer to: " + baseLayerValue);
                    baseLayerGroup.getLayers().forEach(function(element, index, array) {
                      let baseLayerTitle = element.get('title');
                      element.setVisible(baseLayerTitle === baseLayerValue);
                    });
                  })
                }
        */

        /** Register event listner when Projection is changed.
         * Rebuild pins and polygons and update map view */
        var ch = document.getElementsByName('map-res-projection');
        if (selected_proj != null) {
          document.getElementById(selected_proj).checked = true;
        } else {
          document.getElementById(init_proj).checked = true;
        }
        //document.getElementById(init_proj).checked = true;
        for (var i = ch.length; i--;) {
          ch[i].onchange = function change_projection() {
            prj = this.value;
            proj = prj;
            selected_proj = prj;
            console.log("change projection event: " + prj);

            //Update session information with user selected projection
            /* Send the bboundingbox back to drupal metsis search controller to add the current boundingbox filter to the search query */
            var myurl = '/metsis/search/map/projection?&proj=' + selected_proj;
            console.log('calling controller url: ' + myurl);
            let data = Drupal.ajax({
              url: myurl,
              async: true
            }).execute();

            //Do something after ajax call are complete
            //$(document).ajaxComplete(function(event, xhr, settings) {
            //  console.log('ajax complete:' + drupalSettings.metsis_search_map_block.proj);
            // selected_proj = drupalSettings.metsis_search_map_block.proj;

            //});

            //Remove pins ans polygons
            console.log("Remove pins and polygons layers");
            featureLayersGroup.getLayers().clear();

            console.log("Update view to new selected projection: " + prj);
            console.log(projObjectforCode[prj].projection);
            console.log("Features extent: " + featuresExtent);
            map.setView(new ol.View({
              minZoom: 0,
              maxZoom: 23,
              center: ol.extent.getCenter(featuresExtent),
              extent: projObjectforCode[prj].extent,
              //maxResolution: 43008.234375,
              //projection: projObjectforCode[prj].projection,
              projection: selected_proj,
            }));
            console.log("Rebuild pins and polygons features with projection: " + prj);
            featuresExtent = buildFeatures(projObjectforCode[prj].projection);

            //If wms layers a

            //Zoom to new extent
            map.getView().fit(featuresExtent);
            map.getView().setZoom(map.getView().getZoom() - 0.3);
            wmsLayerGroup.getLayers().forEach(function (layer, index, array) {
              if (layer instanceof ol.layer.Tile) {
                layer.getSource().updateParams({ 'CRS': selected_proj });
                layer.getSource().refresh();
              }
              else {
                layer.getLayers().forEach(function (layer, index, array) {
                  if (layer instanceof ol.layer.Tile) {
                    layer.getSource().updateParams({ 'CRS': selected_proj });
                    layer.getSource().refresh();
                  }
                });
              }
            });
            progress_bar()
          }

        }
        //Create a popup with information:
        /**
         * Elements that make up the popup.
         */
        var popUpContainer = document.getElementById('popup');
        //var content = document.getElementById('popup-content');
        var popUpContent = $("#popup-content");
        var popUpCloser = document.getElementById('popup-closer');

        /**
         * Create an overlay to anchor the popup to the map.
         */
        console.log("Creating popup overlay");
        var popUpOverlay = new ol.Overlay({
          element: popUpContainer,
          autoPan: true,
          autoPanAnimation: {
            duration: 150,
          },
        });

        /**
         * Add a click handler to hide the popup.
         * @return {boolean} Don't follow the href.
         */
        console.log("Register popUp closer event");
        popUpCloser.onclick = function () {
          popUpOverlay.setPosition(undefined);
          popUpCloser.blur();
          map.addOverlay(overlayh);
          return false;
        };

        /** Add tooltip overlay to map */
        if (debug) {
          console.log("Creating tooltip overlay");
        }
        // title on hover tooltip
        var tlphovMapRes = document.createElement("div");
        tlphovMapRes.setAttribute("id", "tlphov-map-res")

        var overlayh = new ol.Overlay({
          element: tlphovMapRes,
        });
        //  map.addLayer(layer['OSM']);
        //map.addOverlay(overlayh);
        /** Create custom features and styles */

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
        }); var iconStyleBk = new ol.style.Style({
          image: new ol.style.Icon(({
            anchor: [0.5, 0.0],
            anchorOrigin: 'bottom-left',
            anchorXUnits: 'fraction',
            anchorYUnits: 'fraction',
            opacity: 1.00,
            src: '/' + path + '/icons/pinBk.png'
          }))
        });

        /**
         * Define different basemaps layers to choose from here.
         * Using layergroups and radio selection
         */

        const osmStandard = new ol.layer.Tile({
          title: 'OSMStandard',
          baseLayer: true,
          visible: true,
          source: new ol.source.OSM({}),
        });

        const osmHumanitarian = new ol.layer.Tile({
          title: 'OSMHumanitarian',
          baseLayer: true,
          visible: false,
          source: new ol.source.OSM({
            url: 'https://{a-c}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
            crossOrigin: 'anonymous',
          }),
        });
        const yandex = new ol.layer.Tile({
          title: "Yandex",
          baseLayer: true,
          visible: false,
          source: new ol.source.XYZ({
            url: 'https://sat0{1-4}.maps.yandex.net/tiles?l=sat&x={x}&y={y}&z={z}',
            maxZoom: 15,
            transition: 0,
            //opaque: true,
            attributions: '© Yandex',
            crossOrigin: 'anonymous',
          }),
        });

        const esriSatellite = new ol.layer.Tile({
          title: "ESRI",
          baseLayer: true,
          visible: false,
          source: new ol.source.XYZ({
            attributions: ['Powered by Esri',
              'Source: Esri, DigitalGlobe, GeoEye, Earthstar Geographics, CNES/Airbus DS, USDA, USGS, AeroGRID, IGN, and the GIS User Community'
            ],
            attributionsCollapsible: false,
            url: 'https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            maxZoom: 23,
            crossOrigin: 'anonymous',
          }),
        });

        const stamenTerrain = new ol.layer.Tile({
          title: "stamenTerrain",
          baseLayer: true,
          visible: false,
          source: new ol.source.XYZ({
            attributions: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.',
            url: 'https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg',
            crossOrigin: 'anonymous',
          }),
        });


        const europaVeg = new ol.layer.Tile({
          title: "Europaveg",
          baseLayer: false,
          visible: false,
          source: new ol.source.TileWMS({
            url: 'https://wms.geonorge.no/skwms1/wms.vegnett2',
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

        const riksVeg = new ol.layer.Tile({
          title: "Riksveg",
          baseLayer: false,
          visible: false,
          source: new ol.source.TileWMS({
            url: 'https://wms.geonorge.no/skwms1/wms.vegnett2',
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


        const fylkesVeg = new ol.layer.Tile({
          title: "Fylkesveg",
          baseLayer: false,
          visible: false,
          source: new ol.source.TileWMS({
            url: 'https://wms.geonorge.no/skwms1/wms.vegnett2',
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



        //Create a layergroup to hold the different basemaps
        const baseLayerGroup = new ol.layer.Group({
          title: 'Base Layers',
          //openInLayerSwitcher: true,
          layers: [
            osmStandard, osmHumanitarian, stamenTerrain, esriSatellite
          ],
        });

        //Create a layergroup to hold the different basemaps
        const additonalLayerGroup = new ol.layer.Group({
          title: 'Additional Layers',
          openInLayerSwitcher: true,
          layers: [
            europaVeg, riksVeg, fylkesVeg
          ],
        });

        // create layergroup to hold wmsLayers
        const wmsLayerGroup = new ol.layer.Group({
          title: "WMS Layers",
          openInLayerSwitcher: true,
          layers: [],
        });

        //Variable to hold timeDimensions for wms timeSeries
        var timeDimensions = [];
        var elevationDimensions = [];
        var elevationUnits = 'NA';
        //Create features Layergroup
        var featureLayers = {};
        var featureLayersGroup = new ol.layer.Group({
          title: 'Features',
          //openInLayerSwitcher: true,
          visible: true,
          layers: [],
        });

        //Add overviewMap
        // var bboxLayer = getActiveBbox(selected_proj);
        // var ovMapLayers = [];
        // var ovBaseLayer = new ol.layer.Tile({
        //   //baseLayer: true,
        //   visible: true,
        //   source: new ol.source.OSM(),
        //   projection: selected_proj,
        // });
        // ovMapLayers.push(ovBaseLayer);
        // if (bboxLayer != null) {
        //   console.log("Adding bbox to overviewMap");
        //   ovMapLayers.push(bboxLayer);
        // }

        //Add MapControls

        //Add OverVoewMapControl
        // var ovMapControl = new ol.control.OverviewMap({
        //   //className: 'ol-overviewmap bboxViewMap',
        //   title: 'overviewMap',
        //   layers: ovMapLayers,
        //   collapsed: true,
        // });

        //Add fullScreenControl
        var fullScreenControl = new ol.control.FullScreen({
          source: 'mapcontainer',
          //className: 'fullscreen',
        });

        fullScreenControl.on('enterfullscreen', function () {
          console.log("Entered fullscreen");
          //Update the mapsize
          if (timeDimensions.length > 0) {
            $('#map-res').height($('.mapcontainer:fullscreen').height() - $('.bottom-map-panel').height() - 30);
            $('.map-sidepanel').height($('.mapcontainer:fullscreen').height() - $('.bottom-map-panel').height() - 30);
          } else {
            $('#map-res').height($('.mapcontainer:fullscreen').height());
            $('.map-sidepanel').height($('.mapcontainer:fullscreen').height());
          }
          setTimeout(function () {

            map.updateSize();
            map.getView().setCenter(ol.extent.getCenter(featuresExtent));
            //map.getView().fit(featuresExtent, { size: map.getSize() });
            map.getView().fit(featuresExtent);
            map.getView().setZoom(map.getView().getZoom() - 0.1);
          }, 200);

          //Update the position of the layer control button
          //$('.map-openbtn-wrapper').css("top", "8.8em");
          //map.getView().fit(featuresExtent, { size: map.getSize() });
          /*         baseLayerGroup.getLayers().forEach( function(element, index, array) {
                     if(element.getVisible()) {
                       element.getSource().refresh();
                     }
                   });
          */
          //  map.updateSize();
        });
        fullScreenControl.on('leavefullscreen', function () {
          console.log("Leaved fullscreen");
          //Update the mapsize
          $('#map-res').height("450px");
          $('#map-sidepanel').height("450px");
          setTimeout(function () {
            map.updateSize();
            map.getView().setCenter(ol.extent.getCenter(featuresExtent));
            //map.getView().fit(featuresExtent, { size: map.getSize() });
            map.getView().fit(featuresExtent);
            map.getView().setZoom(map.getView().getZoom() - 0.3);
            //$('.map-openbtn-wrapper').css("top", "11.8em");

          }, 200);

          //map.getView().fit(featuresExtent, { size: map.getSize() });
          /*         baseLayerGroup.getLayers().forEach( function(element, index, array) {
                     if(element.getVisible()) {
                       element.getSource().refresh();
                     }
                   });
                   //map.getView().changed();
          */

        });
        //Add scaleline control
        var scaleLineControl = new ol.control.ScaleLine();


        //Mouseposition lat lon
        var mousePositionControl = new ol.control.MousePosition({
          coordinateFormat: function (co) {
            return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
          },
          projection: 'EPSG:4326',
        });

        /* Define the custom sidebar / layerswitcher control */
        var OpenSideBarControl = /*@__PURE__*/ (function (Control) {
          function OpenSideBarControl(opt_options) {
            var options = opt_options || {};

            var button = document.createElement('button');
            button.innerHTML = '&#9776;'

            var element = document.createElement('div');
            element.className = 'map-openbtn-wrapper ol-unselectable ol-control';
            element.appendChild(button);

            /* Populate wms styles */
            console.log("populate wms style select")
            var selectStyles = document.createElement('select');
            selectStyles.className = 'wms-style-dropdown';
            selectStyles.id = 'wms-styles-select';
            selectStyles.name = "WMS Styles";
            var styleLabel = document.createElement("label");
            styleLabel.innerHTML = "Choose WMS style: "
            styleLabel.htmlFor = "wms-styles";
            document.getElementById("wms-style-id").appendChild(styleLabel).appendChild(selectStyles);

            ol.control.Control.call(this, {
              element: element,
              target: options.target,
            });

            button.addEventListener('click', this.handleOpenSideBar.bind(this), false);
          }

          if (Control) OpenSideBarControl.__proto__ = Control;
          OpenSideBarControl.prototype = Object.create(Control && Control.prototype);
          OpenSideBarControl.prototype.constructor = OpenSideBarControl;

          OpenSideBarControl.prototype.handleOpenSideBar = function handleOpenSideBar() {
            function openSideBar() {
              console.log("Opening the sidebar");
              //Check if we are in fullscreen mode or not
              var full_screen_element = document.fullscreenElement;
              if (full_screen_element !== null) {
                console.log("Opening sidebar: fullscreen");
                $('#map-sidepanel').width("20%");
                $('.map-res').width("80%");
              } else {
                console.log("Opening sidebar: normal");
                $('#map-sidepanel').width("30%");
                $('.map-res').width("70%");
              }
              //Update the mapsize
              setTimeout(function () {
                map.updateSize();
              }, 250);
              setTimeout(function () {

                $('#map-sidepanel').show();
              }, 300);
              map.getView().setCenter(ol.extent.getCenter(featuresExtent));
              //map.getView().fit(featuresExtent);
              map.getView().setZoom(map.getView().getZoom());

            }
            if ($('#map-sidepanel').css('display') == 'none') {
              openSideBar()
            } else {
              closeSideBar()
            }
          };

          return OpenSideBarControl;
        }(ol.control.Control));

        var sideBarControl = new OpenSideBarControl();

        /** END Layerswitrcher sidebar control */

        /***** Initialize the map *****************/
        console.log("Creating the map with projection: " + selected_proj);

        function createMap() {
          return new ol.Map({
            target: 'map-res',
            pixelRatio: 1,
            // controls: ol.control.defaults().extend([ovMapControl, sideBarControl, fullScreenControl, scaleLineControl, mousePositionControl]),
            controls: ol.control.defaults().extend([sideBarControl, fullScreenControl, scaleLineControl, mousePositionControl]),
            //controls: ol.control.defaults().extend([fullScreenControl]),
            //layers: [baseLayerGroup,featureLayersGroup],
            layers: [baseLayerGroup, additonalLayerGroup, featureLayersGroup, wmsLayerGroup],
            overlays: [overlayh, popUpOverlay],
            view: new ol.View({
              zoom: 2,
              minZoom: 0,
              maxZoom: 23,
              //rotation: 0.5,
              center: projObjectforCode[selected_proj].center,
              extent: projObjectforCode[selected_proj].extent,
              projection: projObjectforCode[selected_proj].projection,
              //projection: selected_proj,
            }),
          });
        }

        //Unset overflow on canvas (ol-viewport)

        // create a progress bar to show the loading of tiles
        function progress_bar() {
          console.log("Register progress-bar")
          var tilesLoaded = 0;
          var tilesPending = 0;
          //load all S1 and S2 entries
          map.getLayers().forEach(function (layer, index, array) {

            if (layer.get('title') === 'WMS Layers' && layer instanceof ol.layer.Group) {
              console.log(layer instanceof ol.layer.Group);
              layer.getLayers().forEach(function (layer, index, array) {
                //console.log(array.length);
                //console.log(Object.getPrototypeOf(layer));
                if (layer instanceof ol.layer.Group) {
                  layer.getLayers().forEach(function (layer, index, array) {
                    //for all tiles that are done loading update the progress bar
                    //layer.getSource().refresh();
                    layer.getSource().on('tileloadend', function () {
                      tilesLoaded += 1;
                      var percentage = Math.round(tilesLoaded / tilesPending * 100);
                      document.getElementById('progress').style.width = percentage + '%';
                      // fill the bar to the end
                      if (percentage >= 100) {
                        document.getElementById('progress').style.width = '100%';
                        tilesLoaded = 0;
                        tilesPending = 0;
                      }
                    });

                    //for all tiles that are staring to load update the number of pending tiles
                    layer.getSource().on('tileloadstart', function () {
                      ++tilesPending;
                    });
                  });
                }
                else {
                  layer.getSource().on('tileloadend', function () {
                    tilesLoaded += 1;
                    var percentage = Math.round(tilesLoaded / tilesPending * 100);
                    document.getElementById('progress').style.width = percentage + '%';
                    // fill the bar to the end
                    if (percentage >= 100) {
                      document.getElementById('progress').style.width = '100%';
                      tilesLoaded = 0;
                      tilesPending = 0;
                    }
                  });

                  //for all tiles that are staring to load update the number of pending tiles
                  layer.getSource().on('tileloadstart', function () {
                    ++tilesPending;
                  });
                }
              });
            }
          });
          $('#bottomMapPanel').show();

        }
        //Display message instead of empty map when search results are empty
        // if (extracted_info.length === 0) {
        //   console.log("No extracted info");
        //   $('.map-res').empty();
        //   $('.map-res').append('<span class="w3-margin-left w3-center"><h2>No results found! Please refine your search.</h2></span>');
        // } else {
        //   var map = createMap();
        // }
        var map = createMap();
        function init() {
          $('#map-sidepanel').bind('resize', function (e) {
            console.log('Sidepanel resize');
          });

          $('.ol-viewport').bind('resize', function (e) {
            console.log('OL viewport resize');
            map.updateSize();

          });

        }
        //Create sidepanel button:
        /* Set the width of the sidebar to 250px (show it) */
        function openSideBar() {
          console.log("Opening the sidebar");

          //Check if we are in fullscreen mode or not
          var full_screen_element = document.fullscreenElement;
          if (full_screen_element !== null) {
            console.log("Opening sidebar: fullscreen");
            $('#map-sidepanel').width("20%");
            $('.map-res').width("80%");
          } else {
            console.log("Opening sidebar: normal");
            $('#map-sidepanel').width("30%");
            $('.map-res').width("70%");

          }

          //Update the mapsize
          setTimeout(function () {
            map.updateSize();


          }, 250);
          setTimeout(function () {

            $('#map-sidepanel').show();
          }, 300);
          map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          //map.getView().fit(featuresExtent, {
          //  size: map.getSize()
          //});
          map.getView().setZoom(map.getView().getZoom());

        }

        /* Set the width of the sidebar to 0 (hide it) */
        function closeSideBar() {
          console.log("Closing the sidebar");
          $('#map-sidepanel').hide();
          $('.map-res').width("100%");

          //Update the mapsize
          setTimeout(function () {
            map.updateSize();
          }, 350);
          map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          //map.getView().fit(featuresExtent);
          map.getView().setZoom(map.getView().getZoom());
          //  console.log(map.getLayers()[0]);
        }

        //Create open button for sidepanel with click event
        /*        $('.map-openbtn-wrapper').append(
                  $(document.createElement('button')).prop({
                    id: 'sidepanel-expand',
                    name: 'sidepanel-button-open',
                    type: 'button',
                    title: 'Layers sidepanel',
                    //class: 'map-openbtn ol-layerswitcher ol-unselectable ol-control ol-collapsed',
                    style: 'pointer-events: auto;',
                  }).html('&#9776;')
                );
        */
        //Create close button event:
        $('.map-closebtn-wrapper').append(
          $(document.createElement('a')).prop({
            id: 'sidepanel-close',
            name: 'sidepanel-button-close',
            value: '&times;',
            title: 'Close sidepanel',
            type: 'a',
            href: 'javascript:void(0)',
            class: 'map-closebtn',
          }).html('&times;')
        );

        //Register click event listeners for sidebar buttons
        $('#sidepanel-expand').on('click', function () {
          if ($('#map-sidepanel').css('display') == 'none') {
            openSideBar()
          } else {
            closeSideBar()
          }
        });
        $('#sidepanel-close').on('click', closeSideBar);
        /*        $('#sidepanel-close').on('hidden.bs.collapse', function() {
                  map.updateSize();
                });
                $('#sidepanel-expand').on('shown.bs.collapse', function() {
                  map.updateSize();
                });
        */
        /** Callback function for tooltip pointer move event
         Display the title */
        function id_tooltip_h() {
          console.log("Register tooltip hover function.")
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
                } else {
                  tlphovMapRes.innerHTML += feature_ids[id].id + '<br>';
                }
              }
            } else {
              tlphovMapRes.style.display = 'hidden';
            }
          });
        }
        /**
         * TODO: Read this helptext from metsis search configuration */
        //$('#bottomMapPanel').append().text('Interact directly with selected products from the map by clicking on the highlighted features.');
        // clickable ID in tooltop
        //var tlpMapRes = document.createElement("div");
        //tlpMapRes.setAttribute("id", "tlp-map-res");
        //document.getElementById("map-res").appendChild(tlpMapRes);

        var toolclickevent;
        var toolclickevent_new;
        var getProductInfo;
        //New plot service function. Add bokeh plot endpoint as iFrame
        function plot_ts_bokeh(url_o, pywps, selector) {
          var url = pywps + '?url=' + url_o;
          if ($('#map-ts-plot').html().length > 0) {
            $('#map-ts-plot').empty();
          }
          $('#map-ts-plot').html('<iframe src="' + url + '" width="100%" height="725" frameborder=0 scrolling=no allowfullscreen> title="Timeseries Bokeh Plot"</iframe>');

        }

        //Th plost time series manin function
        function plot_ts(url_o, md_ts_id, path, pywps) {
          let loader = '<img class="map-ts-plot-loader" src="/' + path + '/icons/loader.gif">';
          $('#bokeh-map-ts-plot').find('.map-ts-loader').append(loader);
          var variable = $('#bokeh-map-ts-plot').find('#map-ts-var-list').val();
          if ($('#map-ts-plot').html().length > 0) {
            $('#map-ts-plot').empty();
          }

          fetch(pywps + '?get=plot&resource_url=' + url_o + '&variable=' + variable + '&axis=' + $('#bokeh-map-ts-plot').find('#axis').val())
            .then(function (response) {
              return response.json();
            })
            .then(function (item) {
              item.target_id = 'map-ts-plot';
              Bokeh.embed.embed_item(item);
              $('#bokeh-map-ts-plot').find('.map-ts-loader').empty();
            })
        }

        //Function to plot timeSeries reqistered as variable plotTimeserie, used in getProductInfo
        function plotTimeseries(opendap_url) {
          console.log("calling old ts-plot with url: " + opendap_url);

          //Hide SearchMap
          $('#search-map').slideUp();
          $('#map-ts-back').unbind('click');
          $('#map-ts-back').empty();

          //Show ts-bokeh plot:
          $('#bokeh-map-ts-plot').slideDown();
          $('.map-ts-header').css({
            display: 'block'
          });

          //Create back to results button:
          let button = $('#map-ts-back').append(
            $(document.createElement('button')).prop({
              id: 'backToMapButton',
              class: "w3-button w3-small",
            }).html('Back to results map')
          );
          // Register action for click button:
          button.on('click', function () {
            $('#bokeh-map-ts-plot').slideUp();
            $('.map-ts-header').css({
              display: 'none'
            });
            $('#search-map').slideDown();
            $('#map-ts-plot').empty();
            $('#map-ts-var-list').unbind('change');
            $('#bokeh-map-ts-plot').find('.map-ts-vars').empty();
            $('#backToMapButton').unbind('click');
            $('#map-ts-back').empty();
          });

          /*  if ($('#map-ts-plot').html().length > 0 || $('#bokeh-map-ts-plot').find('.map-ts-vars').html().length > 0) {
              $('#map-ts-plot').empty();
              $('#bokeh-map-ts-plot').find('.map-ts-vars').empty();
            } else {*/
          let loader = '<img class="ts-click-loader" src="/core/misc/throbber-active.gif">';
          $('#bokeh-map-ts-plot').find('.map-ts-loader').append(loader);
          console.log('fetching variables');
          fetch(pywpsUrl + '?get=param&resource_url=' + opendap_url)
            .then(response => response.json())
            .then(data => {
              $('#bokeh-map-ts-plot').find('.map-ts-vars').html(
                $(document.createElement('input')).prop({
                  id: 'axis',
                  name: 'axis',
                  value: Object.keys(data),
                  type: 'hidden',
                })
              ).append(
                $(document.createElement('select')).prop({
                  id: 'map-ts-var-list',
                  name: 'var_list',

                }).append(
                  $(document.createElement('option')).text('Choose variable')
                )
              );
              console.log('looping variables');
              for (const variable of data[Object.keys(data)]) {
                var el = document.createElement("option");
                el.textContent = variable;
                el.value = variable;
                $('#bokeh-map-ts-plot').find('#map-ts-var-list').append(el);
              }
              $('#bokeh-map-ts-plot').find('.map-ts-loader').empty();

              $('#bokeh-map-ts-plot').find('#map-ts-var-list').on('change', function () {
                plot_ts(opendap_url, id, path, pywpsUrl)

              });

            });
          //}
        }

        //Function to plot timeSeries reqistered as variable plotTimeserie, used in getProductInfo
        function plotTimeseries2(opendap_url) {
          console.log("calling ts-plot with url: " + opendap_url);

          //Hide SearchMap
          $('#search-map').slideUp();
          $('#map-ts-back').unbind('click');
          $('#map-ts-back').empty();

          //Show ts-bokeh plot:
          $('#bokeh-map-ts-plot').slideDown();
          $('.map-ts-header').css({
            display: 'none'
          });

          //Create back to results button:
          let button = $('#map-ts-back').append(
            $(document.createElement('button')).prop({
              id: 'backToMapButton',
              class: "w3-button w3-small",
            }).html('Back to results map')
          );
          // Register action for click button:
          button.on('click', function () {
            $('#bokeh-map-ts-plot').slideUp();
            $('.map-ts-header').css({
              display: 'none'
            });
            $('#search-map').slideDown();
            $('#map-ts-plot').empty();
            $('#map-ts-var-list').unbind('change');
            $('#bokeh-map-ts-plot').find('.map-ts-vars').empty();
            $('#backToMapButton').unbind('click');
            $('#map-ts-back').empty();
            $('#bokeh-map-ts-plot').css("width", $(window).width());
            $('#bokeh-map-ts-plot').css("height", $(window).height());
            // if already full screen; exit
            // else go fullscreen
            /*if (
              document.fullscreenElement ||
              document.webkitFullscreenElement ||
              document.mozFullScreenElement ||
              document.msFullscreenElement
            ) {
              if (document.exitFullscreen) {
                document.exitFullscreen();
              } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
              } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
              } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
              }
            } else {
              element = $('#bokeh-map-ts-plot').get(0);
              if (element.requestFullscreen) {
                element.requestFullscreen();
              } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
              } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
              } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
              }
            }*/
          });
          /*  $('#bokeh-map-ts-plot').css({
                position: 'fixed',
                top: '0',
                right: '0',
                bottom: '0',
                left: '0'
            }); */
          //  });

          /*  if ($('#map-ts-plot').html().length > 0 || $('#bokeh-map-ts-plot').find('.map-ts-vars').html().length > 0) {
              $('#map-ts-plot').empty();
              $('#bokeh-map-ts-plot').find('.map-ts-vars').empty();
            } else {*/
          /*          let loader = '<img class="ts-click-loader" src="/core/misc/throbber-active.gif">';
                    $('#bokeh-map-ts-plot').find('.map-ts-loader').append(loader);
                    console.log('fetching variables');
                    fetch(pywpsUrl + '?get=param&resource_url=' + opendap_url)
                      .then(response => response.json())
                      .then(data => {
                        $('#bokeh-map-ts-plot').find('.map-ts-vars').html(
                          $(document.createElement('input')).prop({
                            id: 'axis',
                            name: 'axis',
                            value: Object.keys(data),
                            type: 'hidden',
                          })
                        ).append(
                          $(document.createElement('select')).prop({
                            id: 'map-ts-var-list',
                            name: 'var_list',

                          }).append(
                            $(document.createElement('option')).text('Choose variable')
                          )
                        );
                        console.log('looping variables');
                        for (const variable of data[Object.keys(data)]) {
                          var el = document.createElement("option");
                          el.textContent = variable;
                          el.value = variable;
                          $('#bokeh-map-ts-plot').find('#map-ts-var-list').append(el);
                        }
                        $('#bokeh-map-ts-plot').find('.map-ts-loader').empty();
          */
          //            $('#bokeh-map-ts-plot').find('#map-ts-var-list').on('change', function() {
          plot_ts_bokeh(opendap_url, pywpsUrl, '#map-ts_plot');

          //          });

          //});
          //}
        }
        //Hide the animation controls per default
        $('#animatedWmsControls').hide();
        $('#elevationWmsControls').hide();


        //Back in time button function
        var back = function () {
          var val = $("#map-timeslider-id").slider("option", "value");
          var newVal = val - 1;
          if (newVal < 0) {
            newVal = 0;
          }
          $("#map-timeslider-id").slider('value', newVal);
        };

        //Forward in time button function
        var forward = function () {
          var val = $("#map-timeslider-id").slider("option", "value");
          var newVal = val + 1;
          if (newVal === timeDimensions.length) {
            newVal = timeDimensions.length - 1;
          }
          $("#map-timeslider-id").slider('value', newVal);

        };
        //Up in elevation button function
        var up = function () {
          var val = parseInt($("#elevation").attr("data-current"));
          //wmsLayerGroup.setOpacity(ui.value / 100);
          let newVal = val + 1;
          if (newVal > elevationDimensions.length) {
            newVal = elevationDimensions.length;
          }
          if (debug) { console.log("Change elevation up: " + newVal + ', elevation: ' + elevationDimensions[newVal]); }
          var currentElevation = elevationDimensions[newVal];
          wmsLayerGroup.getLayers().forEach(function (element, index, array) {
            //    if(element.getVisible())  {
            element.getLayers().forEach(function (element, index, array) {
              element.getSource().updateParams({
                'ELEVATION': currentElevation,
              });
              element.getSource().refresh();
            });
            //}
            //element.getSource().refresh();
          });
          $("#elevation").attr("data-current", newVal);
          $("#elevation").text(elevationDimensions[newVal] + " " + elevationUnits);
        };

        //Down in elevation button function
        var down = function () {
          var val = parseInt($("#elevation").attr("data-current"));

          //wmsLayerGroup.setOpacity(ui.value / 100);
          let newVal = val - 1;
          if (newVal < 0) {
            newVal = 0;
          }
          if (debug) { console.log("Change elevation down: " + newVal + ', elevation: ' + elevationDimensions[newVal]); }
          var currentElevation = elevationDimensions[newVal];
          wmsLayerGroup.getLayers().forEach(function (element, index, array) {
            //    if(element.getVisible())  {
            element.getLayers().forEach(function (element, index, array) {
              element.getSource().updateParams({
                'ELEVATION': currentElevation,
              });
              element.getSource().refresh();
            });
            //}
            //element.getSource().refresh();
          });
          $("#elevation").attr("data-current", newVal);
          $("#elevation").text(elevationDimensions[newVal] + " " + elevationUnits);
        };

        //Register back forward time button function to buttons
        var forwardButton = document.getElementById('timeForward');
        forwardButton.addEventListener('click', forward, false);

        var backButton = document.getElementById('timeBack');
        backButton.addEventListener('click', back, false)

        var upButton = document.getElementById('elevationUp');
        upButton.addEventListener('click', up, false)

        var downButton = document.getElementById('elevationDown');
        downButton.addEventListener('click', down, false)

        //Check for substrings
        function isSentinelProduct(str, substrings) {
          for (var i = 0; i != substrings.length; i++) {
            var substring = substrings[i];
            if (str.indexOf(substring) != -1) {
              return true;
            }
          }
          return false;
        }

        //TS plot as ajax dialog
        function plotTsDialog(url) {
          var ajaxSettings = {
            url: '/metsis/tsplot/form?url=' + url,
            dialogType: 'modal',
            dialog: { width: '100%', height: window.innerHeight },
          };
          var myAjaxObject = Drupal.ajax(ajaxSettings);
          myAjaxObject.execute();

        }
        //Function for retrieving wms capabilities
        function getWmsLayers(wmsUrl, title, geom) {
          if (wmsUrl != null && wmsUrl != "") {
            //console.log("Got wms resource: " +wmsUrl);
            //console.log("Parsing getCapabilties");
            var getCapString = '?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities';
            var parser = new ol.format.WMSCapabilities();
            var hasTimeDimension = false;
            //Do xml request
            let xhr = new XMLHttpRequest();

            xhr.open('GET', '/metsis/map/getcapfromurl?url=' + wmsUrl, ['sync'])
            //xhr.open('GET', wmsUrl+getCapString)
            xhr.setRequestHeader("Content-Type", "application/xml")
            xhr.setRequestHeader('Accept', 'application/xml')
            xhr.setRequestHeader('Access-Control-Allow-Origin', '*')
            xhr.send()
            // 4. This will be called after the response is received
            xhr.onload = function () {
              if (xhr.status != 200) { // analyze HTTP status of the response
                console.log(`Error ${xhr.status}: ${xhr.statusText}`); // e.g. 404: Not Found
              } else { // show the result
                //console.log(xhr.response); // response is the server response
                var result = parser.read(xhr.response);
                console.log(result);
                //var options = ol.source.WMS.optionsFromCapabilities(result);
                var defaultProjection = result.Capability.Layer.CRS;
                var layers = result.Capability.Layer.Layer;
                var bbox = result.Capability.Layer.EX_GeographicBoundingBox;
                //console.log(defaultProjection);
                //console.log(layers);
                //console.log(bbox);
                for (var idx = 0; idx < layers.length; idx++) {
                  var ls = layers[idx].Layer;
                  if (ls) {
                    for (var i = 0; i < ls.length; i++) {
                      var getTimeDimensions = function () {
                        var dimensions = ls[i].Dimension;
                        if (ls[i].Dimension) {
                          for (var j = 0; j < dimensions.length; j++) {
                            if ("time" === dimensions[j].name) {
                              var times = dimensions[j].values.split(",");
                              return times;
                            }
                          }
                        }
                        return [];
                      };
                      var makeAxisAwareExtent = function () {
                        var bboxs = ls[i].BoundingBox;
                        if (bboxs) {
                          for (var k = 0; k < bboxs.length; k++) {
                            if (result.version === '1.3.0' && bboxs[k].crs === 'EPSG:4326') {
                              //switch minx with min y and max x with maxy
                              var axisAwareExtent = [];
                              axisAwareExtent[0] = bboxs[k].extent[1];
                              axisAwareExtent[1] = bboxs[k].extent[0];
                              axisAwareExtent[2] = bboxs[k].extent[3];
                              axisAwareExtent[3] = bboxs[k].extent[2];
                              return axisAwareExtent;
                            }
                          }
                        }
                        return bboxs[0].extent;
                      };
                      if (getTimeDimensions().length > 0) {
                        hasTimeDimension = true;
                      }
                      var layerProjections = ls[i].CRS;
                      wmsLayerGroup.getLayers().push(
                        new ol.layer.Tile({
                          title: ls[i].Title,
                          visible: true,
                          keepVisible: false,
                          extent: ol.proj.transformExtent(bbox, 'EPSG:4326', selected_proj),
                          //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                          dimensions: getTimeDimensions(),
                          styles: ls[i].Style,
                          source: new ol.source.TileWMS(({
                            url: wmsUrl,
                            reprojectionErrorThreshold: 0.1,
                            //projection: selected_proj,
                            params: {
                              'LAYERS': ls[i].Name,
                              'VERSION': result.version,
                              'FORMAT': 'image/png',
                              'STYLES': (typeof ls[i].Style !== "undefined") ? ls[i].Style[0].Name : '',
                              'TILE': true,
                              'TRANSPARENT': true,
                            },
                            crossOrigin: 'anonymous',
                          })),
                        }));
                    }
                  }
                }

                featureLayersGroup.setVisible(false);

                //Add timeDimension controls if we have timeDimension
                if (hasTimeDimension) {
                  $('#animatedWmsControls').show();
                  updateInfo();
                }
                //Fit to feature geometry
                //console.log(feature_ids[id]);
                map.getView().fit(geom.getExtent());
                //map.getView().fit(wmsLayer.getExtent())
                map.getView().setZoom(map.getView().getZoom());
                progress_bar()
              }
            };

            xhr.onerror = function () {
              alert("Request failed");
            };
            //console.log(layers);

          }
        }

        //Function for creating times dimension array from
        // wms time duration syntax
        function getTimesArray(start, end, duration) {
          var dateArray = [];
          var currentDate = moment(start);
          var stopDate = moment(end);
          var duration = moment.duration(duration);
          while (currentDate <= stopDate) {
            //dateArray.push(moment(currentDate).format('YYYY-MM-DDTHH:MM:SSZ'));
            dateArray.push(moment(currentDate).utc().format());
            currentDate = moment(currentDate).add(duration);
          }
          return dateArray;
        }

        //Function for retrieving wms capabilities
        function getWmsLayers2(wmsUrl, title, geom, wmsLayerMmd) {
          if (wmsUrl != null && wmsUrl != "") {
            console.log("Processing wms visualization");


            //Create a loader for better user experience:
            var img = document.getElementById('mapLoader');
            //img.src = "/" + path + "/icons/loader.gif";
            img.src = '/core/misc/throbber-active.gif';
            //console.log("Got wms resource: " +wmsUrl);
            //console.log("Parsing getCapabilties");
            var getCapString = '?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities';
            var parser = new ol.format.WMSCapabilities();
            var hasTimeDimension = false;
            var defaultTimeDim = null;
            var hasElevationDimension = false;
            var proxyURL = '/metsis/map/getcapfromurl?url=';
            var wmsUrlOrig = wmsUrl;
            var productTitle = title;
            //initialize result varuable
            var result;
            console.log("wms_layer from mmd: " + wmsLayerMmd);
            //Do ajax call.
            //   fetch(wmsUrl+getCapString,{
            //      mode: 'cors',
            //    }).then(function(response) {
            wmsUrl = wmsUrl.replace(/(^\w+:|^)\/\//, '//');
            wmsUrl = wmsUrl.replace('//lustre', '/lustre');
            if (wmsUrl.includes('wms.wps.met.no/get_wms')) {
              wmsUrlOrig = wmsUrl;
            }
            else {
              wmsUrl = wmsUrl.split("?")[0];
              wmsUrlOrig = wmsUrlOrig.split("?")[0];
            }
            console.log("trying wms with url: " + wmsUrl);

            function onGetCapSuccess(response) {
              //console.log(response);
              result = parser.read(response);

              console.log(result);
              //var options = ol.source.WMS.optionsFromCapabilities(result);



              var defaultProjection = result.Capability.Layer.CRS;
              var layers = result.Capability.Layer.Layer;
              var bbox = result.Capability.Layer.EX_GeographicBoundingBox;
              console.log(layers);
              var parentTitle = result.Capability.Layer.Title;
              console.log("Parent title: " + parentTitle);
              var wmsGroup = new ol.layer.Group({
                title: productTitle,
                openInLayerSwitcher: true,
              });
              //console.log(defaultProjection);
              //console.log(layers);
              console.log(bbox);
              for (var idx = 0; idx < layers.length; idx++) {
                var ls = layers[idx].Layer;
                if (!ls) {
                  lst = layers[idx]
                  if (lst) {
                    ls = [lst];
                  }

                }
                if (ls) {
                  console.log(ls.Name);
                  for (let i = 0; i < ls.length; i++) {
                    var getTimeDimensions = function () {
                      var dimensions = ls[i].Dimension;
                      if (ls[i].Dimension) {
                        for (var j = 0; j < dimensions.length; j++) {
                          if ("time" === dimensions[j].name) {
                            var times = dimensions[j].values.split(",");
                            if (times.length == 1 && dimensions[j].values.indexOf('/')) {
                              var startDate = dimensions[j].values.split("/")[0];
                              var endDate = dimensions[j].values.split("/")[1];
                              var duration = dimensions[j].values.split("/")[2];


                              _defaultTimeDim = dimensions[j].default;

                              // console.log("wms2: got timerange. default: " + _defaultTimeDim);
                              // console.log("start: " + startDate);
                              // console.log("end: " + endDate);
                              // console.log("duration: " + duration);


                              if (_defaultTimeDim !== undefined) {
                                defaultTimeDim = _defaultTimeDim;
                                console.log("timedim default: " + defaultTimeDim);
                              }
                              if (startDate === endDate) {
                                times = [startDate];
                              }
                              else {
                                times = getTimesArray(startDate, endDate, duration);
                              }
                              //console.log("New Times array:: " + times);

                            }
                            return times;
                          }
                        }
                      }
                      return [];
                    };
                    var getElevationDimensions = function () {
                      var dimensions = ls[i].Dimension;
                      if (ls[i].Dimension) {
                        for (var j = 0; j < dimensions.length; j++) {
                          if ("elevation" === dimensions[j].name) {
                            elevationUnits = dimensions[j].units;
                            var elevations = dimensions[j].values.split(",");
                            return elevations;
                          }
                        }
                      }
                      return [];
                    };
                    var getWmsStyles = function () {
                      var styles = ls[i].Style;
                      let styleList = [];
                      if (styles !== undefined) {
                        for (const val of styles) {
                          styleList.push(val.Name);

                        }
                      }
                      console.log(styleList);
                      return styleList
                    }
                    var makeAxisAwareExtent = function () {
                      var bboxs = ls[i].BoundingBox;
                      if (bboxs) {
                        for (var k = 0; k < bboxs.length; k++) {
                          if (result.version === '1.3.0' && bboxs[k].crs === 'EPSG:4326') {
                            //switch minx with min y and max x with maxy
                            var axisAwareExtent = [];
                            axisAwareExtent[0] = bboxs[k].extent[1];
                            axisAwareExtent[1] = bboxs[k].extent[0];
                            axisAwareExtent[2] = bboxs[k].extent[3];
                            axisAwareExtent[3] = bboxs[k].extent[2];
                            return axisAwareExtent;
                          }
                        }
                      }
                      return bboxs[0].extent;
                    };
                    let timedim = getTimeDimensions()
                    if (timedim.length > 0) {
                      //console.log(timedim);
                      hasTimeDimension = true;
                    }
                    let elevatedim = getElevationDimensions()
                    if (elevatedim.length > 0) {
                      //console.log(timedim);
                      hasElevationDimension = true;
                    }
                    let wmsLayerStyles = getWmsStyles()
                    if (wmsLayerStyles.length > 0) {
                      wmsStyles = wmsLayerStyles
                    }
                    var layerProjections = ls[i].CRS;
                    console.log(layerProjections);
                    var visible = false;
                    //var extent = ol.proj.transformExtent(makeAxisAwareExtent(), 'EPSG:4326', selected_proj);
                    //var extent = ol.proj.transformExtent(geom.getExtent(), 'EPSG:4326', selected_proj);

                    var title = ls[i].Title;
                    var layerName = ls[i].Name;
                    console.log('title: ' + title + " name: " + layerName)
                    if (layerName === 'lon' || layerName === 'lat') {
                      visible = false;
                    }
                    if (layerName === wmsLayerMmd || title === wmsLayerMmd) {
                      visible = true;
                      styleValues = getWmsStyles();
                      styleValuesUniq = styleValues.reduce(function (prev, cur) {
                        return (prev.indexOf(cur) < 0) ? prev.concat([cur]) : prev;
                      }, []);
                      console.log(styleValuesUniq);
                      let wmsSelect = document.getElementById('wms-styles-select');

                      for (const val of styleValuesUniq) {
                        var option = document.createElement("option");
                        option.value = val;
                        option.text = val;
                        if ($('#wms-styles-select option[value="' + option.value + '"]').length === 0) {
                          $('#wms-styles-select').append('<option value="' + option.value + '">' + option.text + '</option>');
                        }
                        //wmsSelect.appendChild(option);
                      }

                    }
                    else {
                      if (i === 0) {
                        visible = true;
                        styleValues = getWmsStyles();
                        let styleValuesUniq = styleValues.reduce(function (prev, cur) {
                          return (prev.indexOf(cur) < 0) ? prev.concat([cur]) : prev;
                        }, []);
                        console.log(styleValuesUniq);
                        let wmsSelect = document.getElementById('wms-styles-select');
                        for (const val of styleValuesUniq) {
                          var option = document.createElement("option");
                          option.value = val;
                          option.text = val;
                          if ($('#wms-styles-select option[value="' + option.value + '"]').length === 0) {
                            $('#wms-styles-select').append('<option value="' + option.value + '">' + option.text + '</option>');
                          }
                          //wmsSelect.appendChild(option);
                        }
                      }
                      else visible = false;
                    }
                    if (hasTimeDimension) {
                      let newTimeDim = getTimeDimensions();
                      if (newTimeDim.length > timeDimensions.length) {
                        timeDimensions = newTimeDim;
                      }
                    }
                    if (hasElevationDimension) {
                      let newElevationDim = getElevationDimensions();
                      if (newElevationDim.length > elevationDimensions.length) {
                        elevationDimensions = newElevationDim;
                      }
                    }
                    visible = (idx === 0) ? true : false;
                    console.log("i=" + idx + " layer_name: " + ls[i].Name);
                    if ($.inArray(ls[i].Name, wms_layers_skip) === -1) {
                      wmsGroup.getLayers().insertAt(i,
                        new ol.layer.Tile({
                          title: title,
                          visible: visible,
                          //extent: extent,

                          //keepVisible: false,
                          //preload: 5,
                          //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                          dimensions: getTimeDimensions(),
                          styles: ls[i].Style,
                          source: new ol.source.TileWMS(({
                            url: wmsUrl,
                            reprojectionErrorThreshold: 0.1,
                            projection: selected_proj,
                            params: {
                              'LAYERS': ls[i].Name,
                              'VERSION': result.version,
                              'FORMAT': 'image/png',
                              'STYLES': (typeof ls[i].Style !== "undefined") ? ls[i].Style[0].Name : '',
                              'TIME': (hasTimeDimension && timeDimensions != null) ? timeDimensions[0] : '',
                              'TRANSPARENT': true,
                            },
                            crossOrigin: 'anonymous',

                          })),
                        }));
                      console.log("Added layer: " + title + " visible: " + visible);
                    }
                  }
                  //Update timedimension variables for animation
                  //hasTimeDimension = false;

                }

              }
              //})
              wmsGroup.getLayers().getArray().reverse();
              wmsLayerGroup.getLayers().push(wmsGroup);
              //wmsLayerGroup.set('title', productTitle, false);
              featureLayersGroup.setVisible(false);
              // Add controls for wms style change
              //$('#wms-styles-select').change(function () {
              let wmsSelect = document.getElementById("wms-styles-select");
              wmsSelect.addEventListener('change', function handleChange(event) {
                //wmsLayerGroup.setOpacity(ui.value / 100);
                console.log("Selected style: " + event.target.value);
                let selected_style = event.target.value;
                //console.log("currentTime: " +timeDimensions[ui.value])
                wmsGroup.getLayers().forEach(function (element, index, array) {
                  //console.log(element);
                  element.getSource().updateParams({
                    'STYLES': selected_style
                  });
                  if (element.getVisible() == true) {
                    var res = map.getView().getResolution();
                    console.log(element.getSource().getParams().LAYERS);
                    console.log(element.getVisible());
                    var params = {
                      'LAYER': element.getSource().getParams().LAYERS,
                      'STYLE': selected_style
                    };
                    console.log("legend params: " + params);
                    var legendUrl = element.getSource().getLegendUrl(res, params);
                    console.log("Legend url: " + legendUrl);
                    //$('#bottomMapPanel').append('<img id="map-bottom-wms-legend" />');
                    var img = document.getElementById('map-wms-legend');
                    img.src = legendUrl;
                  }
                });
              });


              //Add timeDimension controls if we have timeDimension
              if (hasTimeDimension) {
                console.log("Processing wms with timedimensons");
                $('#animatedWmsControls').show();
                console.log(timeDimensions);
                var maxValue = timeDimensions.length - 1;
                console.log('MAXVALUE=' + maxValue);
                //Add timeSlider
                $("#map-timeslider-id").slider({
                  class: "range-slider",
                  min: 0,
                  value: 0,
                  max: maxValue,
                  step: 1,
                  animate: true,
                  slide: function (e, ui) {
                    //wmsLayerGroup.setOpacity(ui.value / 100);
                    var currentTime = timeDimensions[ui.value];
                    //console.log("currentTime: " +timeDimensions[ui.value])
                    wmsGroup.getLayers().forEach(function (element, index, array) {
                      //    if(element.getVisible())  {
                      element.getSource().updateParams({
                        'TIME': currentTime,
                      });
                      //}
                      element.getSource().refresh();
                    });
                    $('#time').text(timeDimensions[ui.value]);
                  },
                  change: function (e, ui) {
                    //wmsLayerGroup.setOpacity(ui.value / 100);
                    var currentTime = timeDimensions[ui.value];
                    //console.log("currentTime: " +timeDimensions[ui.value])
                    wmsGroup.getLayers().forEach(function (element, index, array) {
                      //console.log(element);
                      element.getSource().updateParams({
                        'TIME': currentTime,
                      });
                    });
                    $('#time').text(timeDimensions[ui.value]);
                  },

                });
                $('#time').text(timeDimensions[0]);
                //var legendUrl = wmsLayerGroup.getLayers().item(0).getSource().getLegendUrl(undefined);
                try {
                  var res = map.getView().getResolution();
                  var legendUrl = wmsGroup.getLayers().item(0).getSource().getLegendUrl(res);
                  var img = document.getElementById('map-wms-legend');
                  img.src = legendUrl;
                }
                catch {
                  console.log("No legendUrl info for layer");
                }
                //$('#bottomMapPanel').show();
                map.updateSize();
              }
              if (hasElevationDimension) {
                console.log("WMS Layer have elevation dimension");
                console.log(elevationDimensions);
                var currentElevation = elevationDimensions[0];
                //console.log("currentTime: " +timeDimensions[ui.value])
                wmsGroup.getLayers().forEach(function (element, index, array) {
                  //    if(element.getVisible())  {
                  element.getSource().updateParams({
                    'ELEVATION': currentElevation,
                  });
                  //}
                  element.getSource().refresh();
                });
                val = $("#elevation").attr("data-current", 0);
                $("#elevation").text(elevationDimensions[0] + " " + elevationUnits);
                $('#elevationWmsControls').show();
              }
              //Fit to feature geometry
              //console.log(feature_ids[id]);
              if (geom == undefined) {
                //map.getView().fit(makeAxisAwareExtent)
                console.log(bbox);
                geom = extent = ol.proj.transformExtent(bbox, 'EPSG:4326', selected_proj);
                map.getView().fit(geom);
              }
              else {
                map.getView().fit(geom.getExtent());
              }
              //map.getView().fit(wmsLayer.getExtent())
              map.getView().setZoom(map.getView().getZoom());

              //Stop the loader:
              //$('#mapLoader').empty();
              console.log("Empty loader");
              document.getElementById('mapLoader').removeAttribute('src');
              progress_bar()

            }

            function tryProxy(proxyURL, wmsUrlOrig) {
              $.ajax({
                type: 'GET',
                url: proxyURL + wmsUrlOrig,
                crossDomain: true,
                //xhrFields: { withCredentials: true },
                dataType: 'xml',
                //headers: { "Access-Control-Allow-Origin": '*' },
                //async: false,
                error: function () {
                  console.log("Request failed: " + proxyURL + wmsUrlOrig);

                },
                success: function (response) {
                  onGetCapSuccess(response)
                },
              });
            }
            if (wmsUrl.includes('wms.wps.met.no/get_wms')) {
              $.ajax({
                type: 'GET',
                url: wmsUrl,
                dataType: 'xml',
                crossDomain: true,
                // xhrFields: { withCredentials: true },
                // headers: { "Access-Control-Allow-Origin": '*' },
                //async: false,
                error: function () {
                  console.log("Request failed: " + wmsUrl + getCapString);
                  console.log("Trying getCapProxy....");
                  tryProxy(proxyURL, wmsUrlOrig)
                },
                success: function (response) {
                  onGetCapSuccess(response)
                },
              });
            }
            else {
              $.ajax({
                type: 'GET',
                url: wmsUrl + getCapString,
                dataType: 'xml',
                crossDomain: true,
                //xhrFields: { withCredentials: true },
                //headers: { "Access-Control-Allow-Origin": '*'},
                //async: false,
                error: function () {
                  console.log("Request failed: " + wmsUrl + getCapString);
                  console.log("Trying getCapProxy....");
                  tryProxy(proxyURL, wmsUrlOrig)
                },
                success: function (response) {
                  onGetCapSuccess(response)
                },
              });
            }
          }




          //console.log(layers);


        }
        function visualiseWmsLayer(wmsResource, id, title, geom, wms_layers) {
          //Check WMS product:
          if (wmsResource != null && wmsResource != "") {
            console.log("Got Sentinel WMS product: " + id);
            console.log("Default wms layer: " + wms_layers);
            //TODO: Do more stuff here with the WMS product
            //var wmsLayers = getWmsLayers(wmsResource, title);
            //wmsResource = wmsResource.replace(/(^\w+:|^)\/\//, '//');
            //console.log("New wmsResource url: " + wmsResource);
            var sentinel1Layers = ['Composites'];
            var sentinel2Layers = ['true_color_vegetation', 'false_color_vegetation', 'false_color_glacier', 'false_color_glacier', 'opaque_clouds', 'cirrus_clouds'];
            var layer_name = 'Composites';
            if (wmsResource.includes("S2")) {
              layer_name = 'true_color_vegetation';
            }
            else if (wms_layers === "Amplitude HH polarisation") {
              layer_name = 'amplitude_hh';
            }
            else if (wms_layers === "Amplitude HV polarisation") {
              layer_name = 'amplitude_hv';
            }
            else if (wms_layers === "Amplitude VV polarisation") {
              layer_name = 'amplitude_vv';
            }
            else if (wms_layers === "Amplitude VH polarisation") {
              layer_name = 'amplitude_vh';
            }
            else if (wms_layers === "True Color Vegetation Composite") {
              layer_name = 'true_color_vegetation';
            }
            else {
              layer_name = 'Composites';
            }
            var wmsUrl = wmsResource;
            wmsUrl = wmsUrl.replace(/(^\w+:|^)\/\//, '//');
            wmsUrl = wmsUrl.split("?")[0];
            console.log("Using layer: " + layer_name);
            wmsLayerGroup.getLayers().push(
              new ol.layer.Tile({
                title: title,
                visible: true,
                //extent: geom.getExtent(),
                //keepVisible: false,
                //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                //dimensions: getTimeDimensions(),
                //styles: ls[i].Style,
                source: new ol.source.TileWMS(({
                  //projection: selected_proj,
                  url: wmsUrl,
                  reprojectionErrorThreshold: 0.1,
                  params: {
                    'LAYERS': layer_name,
                    'VERSION': '1.3.0',
                    'FORMAT': 'image/png',
                    //'STYLES': (typeof ls[i].Style !== "undefined") ? ls[i].Style[0].Name : '',
                    'TILE': true,
                    'TRANSPARENT': true,
                  },
                  crossOrigin: 'anonymous',

                })),
              }));
            featureLayersGroup.setVisible(false);
            //Fit to feature geometry
            //console.log(feature_ids[id]);
            map.getView().fit(geom.getExtent());
            //map.getView().fit(wmsLayer.getExtent())
            map.getView().setZoom(map.getView().getZoom());
            progress_bar()
          }
        }
        /** Action when product is selected on map
         * Show /Hide datasets in results list on search pages
         *
         * More functionality to be added
         */
        function getProductInfo(evt) {
          //overlay.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);
          $('.datasets-row').css('display', 'none');
          $('.ol-viewport').css("overflow", 'visible !important');
          console.log('getProductInfo event');
          var feature_ids = {};
          var feature_wms = {};
          var id = null;

          //Define layer names for sentinel products
          var sentinel1Layers = ['Composites'];
          var sentinel2Layers = ['true_color_vegetation', 'false_color_vegetation', 'false_color_glacier', 'false_color_glacier', 'opaque_clouds', 'cirrus_clouds'];
          var sentinelStrings = ['S1A', 'S1B', 'S2A', 'S2B'];
          //Clear the previous popup content:
          $('#popup-content').empty();
          //$('#popup-content').hide();

          popUpOverlay.setPosition(undefined);

          //Get the current event coordinate
          var coordinate = evt.coordinate;
          var resolution = map.getView().getResolution();

          //console.log(wmsLayerGroup.getLayers().getArray().length);
          //If we have wms layers, check if we have featureInfo for selected coordinate.
          /*    if (!featureLayersGroup.getVisible() && wmsLayerGroup.getLayers().getArray().length > 0) {
                console.log("Fetching wmsfeatureinfo");
                wmsLayerGroup.getLayers().forEach(function(element, index, array) {
                  if (element.getVisible()) {
                    var url = element.getSource().getFeatureInfoUrl(
                      coordinate,
                      resolution,
                      selected_proj, {
                        'INFO_FORMAT': 'application/geojson'
                      }
                    );
                    if (url) {
                      fetch(url)
                        .then(function(response) {
                          return response.text();
                        })
                        .then(function(html) {
                          console.log(html)
                          //$('#bottomMapPanel').append(html);
                          //$('#bottomMapPanel').show(html);
                          var layer = new ol.layer.Vector({
                            title: 'WMS Features',
                            source: new ol.source.Vector({
                              features: (new ol.format.GeoJSON()).readFeatures(html)
                            }),
                            style: new ol.style.Style({
                              stroke: new ol.style.Stroke({
                                color: "black",
                                width: 10
                              }),
                            })
                          });
                        //featureLayersGroup.getLayers().push(layer);
                        map.getLayers().push(layer);
                        });
                    }
                  }
                });

              } */
          //overlayh.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);
          //Foreach feature selected. do the following
          map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
            console.log("Clicked feature: " + feature.get('name'));
            /* Show / Hide results depending on selected dataset in map */
            id = feature.get('id');
            newId = id.replace(/_/g, "-");
            newId = newId.replace(/\./g, "");
            //newId = id.replace(/:/g, "-");
            //alert(newId);
            console.log('newid: ' + newId);
            //$('.datasets-' + newId).css('display', 'block');
            $('.datasets-' + newId).slideDown();
            //$('._'+newId).css('display', 'block');
            //$(document).ready(function() {
            //  $('li.datasets-' + newId).focus();
            //});
            // $(feature.get('id')).each(function() {
            //$(this).css('display', 'block');
            //});
            //});
            //Reload the lazy loading of thumbnails
            //var bLazy = new Blazy();
            //bLazy.revalidate();
            //console.log("Got " + feature.get('name') + " at coordinate:");
            //console.log(coordinate);
            //overlay.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);
            /** WMS RENDER ON CLICK */
            //IF selected Product have WMS layer. Render this WMS and Zoom to extent.
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
              timeStart: feature.get('time')[0],
              timeEnd: feature.get('time')[1],
              featureType: feature.get('feature_type'),
              wms_layer: feature.get('wms_layer'),
              name: feature.get('name'),
              geom: feature.getGeometry(),
            };
          });
          //Add Popup if selected more than one feature:
          var numberOfFeatures = Object.keys(feature_ids).length;
          console.log("Number of selected features: " + numberOfFeatures);
          if (numberOfFeatures === 0) {
            $('.datasets-row').css('display', 'block');
            //Remove overlay
            map.removeOverlay(overlayh);
            map.addOverlay(overlayh);
          }
          if (numberOfFeatures === 1) {
            //Remove overlay
            map.removeOverlay(overlayh);
            console.log("Execute action for ONE feature");
            var wmsResource = feature_ids[id].url_w;
            var odResource = feature_ids[id].url_o;
            var featureType = feature_ids[id].featureType;
            var title = feature_ids[id].title;
            var wmsLayer = feature_ids[id].wms_layer;

            console.log('product_id: ' + id);
            console.log('product title: ' + feature_ids[id].title);
            console.log('wms resource: ' + wmsResource);
            console.log('latlon: ' + feature_ids[id].latlon);
            console.log('extent: ' + feature_ids[id].extent);
            console.log('feature type: ' + feature_ids[id].featureType);
            console.log('projection: ' + selected_proj);

            //Check for timeseries product
            if (odResource != null && odResource != "") {
              if (featureType === 'timeSeries' || featureType === 'timeSeriesProfile' || featureType === 'profile') {
                console.log("Got timeseries product: " + feature_ids[id].id);
                $('#popup-content').append("<p>" + feature_ids[id].title + "</p>");
                var button = $('#popup-content').append(
                  $(document.createElement('button')).prop({
                    class: "w3-button w3-small",
                  }).html('Visualise timeseries')

                );

                console.log("Alter the popUpOverlay position.");
                popUpOverlay.setPosition(coordinate);
                button.on('click', function () {
                  plotTsDialog(odResource)
                  //plotTimeseries2(odResource)
                  //plotTimeseries(odResource)
                });
              }
            }

            //Check WMS product:
            if (wmsResource != null && wmsResource != "") {
              console.log("Got WMS product: " + feature_ids[id].id);

              //TODO: Do more stuff here with the WMS product
              //var wmsLayers = getWmsLayers(wmsResource, title);
              //console.log(wmsLayers);
              //wmsResource = wmsResource.replace(/(^\w+:|^)\/\//, '//');
              //console.log("New wmsResource url: " + wmsResource);
              /*              wmsLayerGroup.getLayers().push(
                              new ol.layer.Tile({
                                title: title,
                                visible: true,
                                //keepVisible: false,
                                //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                                //dimensions: getTimeDimensions(),
                                //styles: ls[i].Style,
                                source: new ol.source.TileWMS(({
                                  projection: selected_proj,
                                  url: wmsResource,
                                  reprojectionErrorThreshold: 0.1,
                                  params: {
                                    'LAYERS': 'Composites',
                                    'VERSION': '1.3.0',
                                    'FORMAT': 'image/png',
                                    //'STYLES': (typeof ls[i].Style !== "undefined") ? ls[i].Style[0].Name : '',
                                    'TILE': true,
                                    'TRANSPARENT': true,
                                  },
                                  crossOrigin: 'anonymous',

                                })),
                              }));
                            featureLayersGroup.setVisible(false);


                            //Fit to feature geometry
                            //console.log(feature_ids[id]);
                            map.getView().fit(feature_ids[id].geom.getExtent());
                            //map.getView().fit(wmsLayer.getExtent())
                            map.getView().setZoom(map.getView().getZoom());
              */

              /*If we have sentinel prducts, assume no timedimension and standard layer name Composites.
               call the simple visualiseWmsLayer function */
              if (isSentinelProduct(wmsResource, sentinelStrings)) {
                console.log("Creating wms layers for sentinel products");
                visualiseWmsLayer(wmsResource, id, title, feature_ids[id].geom, wmsLayer);
                //getWmsLayers2(wmsResource, title, feature_ids[id].geom)
              }
              /* Else we call function that add all layers and timedimensions */
              else {
                console.log("Creating wms layers for general products");
                getWmsLayers2(wmsResource, title, feature_ids[id].geom, wmsLayer)
              }
            }
          }
          if (numberOfFeatures > 1) {
            console.log("Execute action for multiple features: " + numberOfFeatures);
            //Remove overlay
            map.removeOverlay(overlayh);
            //Loop over the selected features, and create EventListener when selecting one product from list
            let markup = "";
            markup += '<div id="popup-content-div">';
            markup += '<p class="w3-large"> Select product: </p>';
            markup += '<ul class="w3-ul w3-hoverable">';
            for (var key in feature_ids) {
              let id = feature_ids[key].id;
              //$('#popup-content').append('<li id="'+id+ '" class="productItem w3-small w3-hover-blue 3-hover-opacity">'+feature_ids[key].title+'</li>');
              markup += '<li id="popup-lst-' + id + '" data-id="' + id + '" class="popupProductItem w3-small w3-hover-blue 3-hover-opacity">' + feature_ids[key].title + '</li>';
            }
            markup += '</ul>';
            markup += '</div>';
            markup += '<div id="popupSelectedProduct" class="popup-selected-product">';
            markup += '<p><a href="#" id="popupSelectProductBack">&larr; Back</a></p>';
            markup += '<div id="popupSelectedProductContent"></div></div>';
            //$('#popup-content').append('</ul>');
            //$('#popup-content').append('</div>');
            $('#popup-content').append(markup);

            $('#popupSelectProductBack').on('click', function () {
              $('#popup-content-div').show();
              $('#popupSelectedProduct').hide();
              $('#popupSelectedProductContent').empty();
              return false;
            });
            $('.popupProductItem').on(
              'click',
              function () {
                let selected_id = $(this).data('id');
                let selected_title = feature_ids[selected_id].title;
                let wmsResource = feature_ids[selected_id].url_w;
                let odResource = feature_ids[selected_id].url_o;
                let featureType = feature_ids[selected_id].featureType;
                let title = feature_ids[selected_id].title;
                let geom = feature_ids[selected_id].geom;

                console.log("Selected id: " + selected_id);
                console.log("Selected title: " + selected_title);

                //Hide and show some divs
                $('#popup-content-div').hide();
                $('#popupSelectedProduct').show();

                //Add info about the selected dataset
                let productMarkup = '<strong>' + selected_title + '</strong>';

                //Add show metadata details button
                productMarkup += '<p><button id="popupShowMetadata" class="w3-button w3-small">Metadata details</button>';
                productMarkup += '</p>'
                $('#popupSelectedProductContent').append(productMarkup);

                //Add open drupal dialog event to metadata details button.
                var ajaxSettings = {
                  url: '/metsis/metadata/' + selected_id,
                  dialogType: 'modal',
                  dialog: {
                    width: '80%',
                    title: 'Metadata details',
                    topOffset: '100px',
                    autoResize: true,
                    maxHeight: '95%'
                  },
                };
                $('#popupShowMetadata').on('click', function () {
                  var myAjaxObject = Drupal.ajax(ajaxSettings);
                  myAjaxObject.execute();

                })
                //If we have wmsResource, display visulise wms button
                if (wmsResource != null && wmsResource != "") {
                  $('#popupSelectedProductContent').append(
                    $(document.createElement('button')).prop({
                      id: 'popup-wms-button',
                      class: "w3-button w3-small",
                    }).html('Visualise WMS')
                  );
                  $('#popup-wms-button').on('click', function () {
                    //$('.ol-popup').hide();
                    popUpOverlay.setPosition(undefined);
                    //visualiseWmsLayer(wmsResource,id,title, geom)
                    if (isSentinelProduct(wmsResource, sentinelStrings)) {
                      visualiseWmsLayer(wmsResource, selected_id, title, feature_ids[selected_id].geom, feature_ids[id].wms_layer)
                    }
                    /* Else we call function that add all layers and timedimensions */
                    else {
                      getWmsLayers2(wmsResource, title, feature_ids[selected_id].geom, feature_ids[id].wms_layer)
                    }
                  });

                }

                //Check for timeseries product
                if (odResource != null && odResource != "") {
                  if (feature_ids[selected_id].featureType === 'timeSeries' || feature_ids[selected_id].featureType === 'timeSeriesProfile' || feature_ids[selected_id].featureType === 'profile') {
                    console.log("Got timeseries product: " + feature_ids[selected_id].id);
                    //$('#popupSelectedProductContent').append("<p>" + feature_ids[selected_id].title + "</p>");
                    $('#popupSelectedProductContent').append(
                      $(document.createElement('button')).prop({
                        id: 'popup-ts-button',
                        class: "w3-button w3-small",
                      }).html('Visualise timeseries')

                    );

                    console.log("Alter the popUpOverlay position.");
                    $('#popup-ts-button').on('click', function () {
                      popUpOverlay.setPosition(undefined);
                      plotTsDialog(odResource)
                      //plotTimeseries2(odResource)
                      //plotTimeseries(odResource)
                    });
                  }
                }


                //alert($(this).data('id'));
              }
            );


            console.log("Alter the popUpOverlay position.");
            popUpOverlay.setPosition(coordinate);
          } else {
            console.log("No feature selected");
            //$('.datasets-row').css('display', 'block');
            //var bLazy = new Blazy();
            //bLazy.revalidate();
          }

          //});
        }

        function id_tooltip() {
          //var tooltip = document.getElementById('tlp-map-res');
          console.log('inside id_tooltip');

          map.on('click', tooltipclick);
        }

        function id_tooltip_new() {
          //var tooltip = document.getElementById('tlp-map-res');
          console.log('Register product select event');

          map.on('singleclick', getProductInfo);
        }

        //build up the point/polygon features
        function buildFeatures(prj) {
          console.log("Building polygons and pins features....");
          var allFeatures = [];
          var iconFeaturesPol = [];
          var iconFeaturesPin = [];
          var wmsProducts = [];
          if (extracted_info.length === 0) {
            console.log("No extracted Info")
            var featuresExtent = new ol.extent.createEmpty();

            return featuresExtent;
          }
          for (var i12 = 0; i12 <= extracted_info.length - 1; i12++) {

            //console.log(extracted_info[i12]);

            //If we have a geographic extent, create polygon feature
            if ((extracted_info[i12][2][0].toFixed(4) !== extracted_info[i12][2][1].toFixed(4)) || (extracted_info[i12][2][2].toFixed(4) !== extracted_info[i12][2][3].toFixed(4))) {
              //Transform boundingbox to selected projection and create a polygon geometry
              box_tl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][0]], 'EPSG:4326', prj);
              box_tr = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][0]], 'EPSG:4326', prj);
              box_bl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][1]], 'EPSG:4326', prj);
              box_br = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][1]], 'EPSG:4326', prj);
              geom = new ol.geom.Polygon([
                [box_tl, box_tr, box_br, box_bl, box_tl]
              ]);
              // Handle wide extents in EPSG:32661
              var west = -6378137.0 * Math.PI;
              var east = 6378137.0 * Math.PI;

              if (extracted_info[i12][2][2] === 180 && selected_proj === "EPSG:32661") {
                extracted_info[i12][2][2] = east;
              }
              if (extracted_info[i12][2][3] === -180 && selected_proj === "EPSG:32661") {
                extracted_info[i12][2][3] = west;
              }
              if (extracted_info[i12][2][2] === 179.9 && selected_proj === "EPSG:32661") {
                extracted_info[i12][2][2] = east;
              }
              if (extracted_info[i12][2][3] === -179.9 && selected_proj === "EPSG:32661") {
                extracted_info[i12][2][3] = west;
              }
              if ((extracted_info[i12][2][3] === west && extracted_info[i12][2][2] === east && selected_proj == "EPSG:32661")
                || (extracted_info[i12][2][3] === west + 0.1 && extracted_info[i12][2][2] === east - 0.1 && selected_proj == "EPSG:32661")) {
                console.log("Processing wide polygon");
                console.log("wide dataset:" + extracted_info[i12][4][0]);
                // Transform the North and South coordinates to EPSG:32661
                var north = ol.proj.transform([0, extracted_info[i12][2][0]], 'EPSG:4326', prj)[1];
                var south = ol.proj.transform([0, extracted_info[i12][2][1]], 'EPSG:4326', prj)[1];


                geom = new ol.geom.Polygon.fromExtent([west, south, east, north]);
              }

              //Define polygon features
              var iconFeaturePol = new ol.Feature({
                url: extracted_info[i12][0],
                id: extracted_info[i12][1],
                geometry: geom,
                extent: [extracted_info[i12][2][0], extracted_info[i12][2][1], extracted_info[i12][2][2], extracted_info[i12][2][3]],
                latlon: extracted_info[i12][3],
                title: extracted_info[i12][4],
                abs: extracted_info[i12][5],
                time: [extracted_info[i12][6][0], extracted_info[i12][6][1]],
                //thumb: extracted_info[i12][7],
                related_info: extracted_info[i12][8],
                iso_keys_coll_act: extracted_info[i12][9],
                info_status: extracted_info[i12][10],
                data_center: extracted_info[i12][11],
                actions: extracted_info[i12][12],
                contacts: extracted_info[i12][13],
                constraints: extracted_info[i12][14],
                core: extracted_info[i12][15],
                feature_type: extracted_info[i12][16],
                wms_layer: extracted_info[i12][17],
                name: "Polygon Feature",
                //projection: prj,
              });
              iconFeaturePol.setId(extracted_info[i12][1]);
              iconFeaturesPol.push(iconFeaturePol);
              allFeatures.push(iconFeaturePol);

              iconFeaturePol.setStyle(featureStyleBl);

            }
            // Else we assume geographic extent is a point, and create a pin feature
            else {
              geom = new ol.geom.Point(ol.proj.transform([extracted_info[i12][3][1], extracted_info[i12][3][0]], 'EPSG:4326', prj));
              //Define pin features
              var iconFeaturePin = new ol.Feature({
                url: extracted_info[i12][0],
                id: extracted_info[i12][1],
                geometry: geom,
                extent: [extracted_info[i12][2][0], extracted_info[i12][2][1], extracted_info[i12][2][2], extracted_info[i12][2][3]],
                latlon: extracted_info[i12][3],
                title: extracted_info[i12][4],
                abs: extracted_info[i12][5],
                time: [extracted_info[i12][6][0], extracted_info[i12][6][1]],
                //thumb: extracted_info[i12][7],
                related_info: [extracted_info[i12][8][0], extracted_info[i12][8][1]],
                iso_keys_coll_act: extracted_info[i12][9],
                info_status: extracted_info[i12][10],
                data_center: extracted_info[i12][11],
                actions: extracted_info[i12][12],
                contacts: extracted_info[i12][13],
                constraints: extracted_info[i12][14],
                core: extracted_info[i12][15],
                feature_type: extracted_info[i12][16],
                wms_layer: extracted_info[i12][17],
                name: "Pin Feature",
              });
              iconFeaturePin.setId(extracted_info[i12][1]);
              iconFeaturesPin.push(iconFeaturePin);
              allFeatures.push(iconFeaturePin);

              if ((extracted_info[i12][2][0] !== extracted_info[i12][2][1]) || (extracted_info[i12][2][2] !== extracted_info[i12][2][3])) {
                iconFeaturePin.setStyle(iconStyleBl);
              } else {
                iconFeaturePin.setStyle(iconStyleBk);
              }
            }

          }

          //create a vector source with all points
          var vectorSourcePol = new ol.source.Vector({
            features: iconFeaturesPol,
            name: 'polygonSource',
            projection: prj,
          });

          //create a vector layer with all points from the vector source and pins
          var polygonsFeatureLayer = new ol.layer.Vector({
            title: 'Polygons',
            name: 'polygonsLayer',
            visible: true,
            //projection: prj,
            source: vectorSourcePol,
          });

          //create a vector source with all points
          var vectorSourcePin = new ol.source.Vector({
            features: iconFeaturesPin,
            name: 'pinsSource',
            projection: prj,
            style: function styleFunction(feature, resolution) {
              var zoom = map.getView().getZoom();
              var area = feature.getGeometry().getArea();

              // Adjust these values as needed.
              var zoomThreshold = 10;
              var areaThreshold = 1000;

              if (zoom <= zoomThreshold && area <= areaThreshold) {
                // If the map is zoomed out and the polygon is small, use the pin style.
                return pinStyle;
              } else {
                // Otherwise, use the polygon style.
                return polygonStyle;
              }
            }
          });
          var pinsFeatureLayer = new ol.layer.Vector({
            title: 'Pins',
            name: 'pinsLayer',
            visible: true,
            source: vectorSourcePin,
            //projection: prj,
            //style: iconStyle,
          });

          //console.log(newFeature);

          featureLayersGroup.getLayers().push(polygonsFeatureLayer);
          featureLayersGroup.getLayers().push(pinsFeatureLayer);

          //Fit to extent of features
          var featuresExtent = new ol.extent.createEmpty();
          allFeatures.forEach(function (feature) {
            featuresExtent = new ol.extent.extend(featuresExtent, feature.getGeometry().getExtent());
          });
          //var maxExt = extent.getExtent();
          console.log("Adding feature layers to map");
          //map.addLayer(featureLayers['polygons']);
          //map.addLayer(featureLayers['pins']);

          return featuresExtent
        }

        //initialize features
        console.log("Building features with projection: " + selected_proj);
        var featuresExtent = buildFeatures(projObjectforCode[selected_proj].projection);
        console.log(featuresExtent);
        if (!ol.extent.isEmpty(featuresExtent)) {
          map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          map.getView().fit(featuresExtent);
        }
        map.getView().setZoom(map.getView().getZoom() - 0.3);
        // display clickable ID in tooltip
        //console.log('calling id_tooltip');
        //id_tooltip()

        //Register the tooltip and prosuct select actions
        id_tooltip_new()
        id_tooltip_h()
        init()
        progress_bar()
        $('#map-sidepanel').hide();
        $('#bottomMapPanel').hide();

        /*var provider = new OpenStreet({
          params: {
            countrycodes: "no,sj,se,dk,is"
          }
        })*/

        //Kartverket provider
        const geonorgeProvider = geoNorgeSearch({
          url: 'https://ws.geonorge.no/SKWS3Index/ssr/sok?',
        });

        //Add geocoder search
        let geocoder = new Geocoder('nominatim', {
          provider: 'osm',
          //geonorgeProvider,
          lang: 'nb-NO', //en-US, fr-FR
          placeholder: 'Search for ...',
          targetType: 'glass-button',
          limit: 5,
          keepOpen: true,
          autoComplete: true,
          countrycodes: 'no,sj,se,dk,is,fo,fi,gb'
        });

        geocoder.on('addresschosen', function (evt) {
          // it's up to you
          console.info(evt);
          var bbox = evt.place.bbox;
          /* Send the bboundingbox back to drupal metsis search controller to add the current boundingbox filter to the search query */
          var myurl = '/metsis/search/place?tllat=' + bbox[1] + '&tllon=' + bbox[2] + '&brlat=' + bbox[0] + '&brlon=' + bbox[3] + '&proj=' + selected_proj;
          console.log('calling controller url: ' + myurl);
          data = Drupal.ajax({
            url: myurl,
            async: false,
            success: function (response) {

              //Store place in browser session
              //sessionStorage.setItem("place_lat", evt.place.lat);
              //sessionStorage.setItem("place_lon", evt.place.lon);

              console.log(window.location.href);

              location.href = window.location.href; //Redirect
            }
          }).execute();

        });
        map.addControl(geocoder);
        //createOverViewMap(selected_proj)

        //Function to zoom to extent of all features:
        function zoomToProductsExtent() {
          console.log("Zoom back to features extent");
          map.getLayers().forEach(function (element, index, array) {
            if (element.get('title') === 'pins') {
              console.log("Set pins layer visible");
              element.setVisible(true);
              element.getSource().refresh();
              if (element.get('title') === 'polygons') { }
              console.log("Set polygon layer visible");
              element.setVisible(true);
              element.getSource().refresh();
            }
          });
          map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          map.getView().fit(featuresExtent);
          map.getView().setZoom(map.getView().getZoom() - 0.3);
        }

        //Function to rebuild features:
        function rebuildbuildFeatures(prj) {
          console.log("transform features gemetry from: " + init_proj + ' to ' + selected_proj);
          featureLayersGroup.getLayers().forEach(function (element, index, array) {
            let features = element.getSource().getFeatures();
            for (let feature of features) {
              //console.log(feature);
              feature.getGeometry().transform(init_proj, prj);
            }
            element.getSource().refresh();
          })
        }

        /* Create a bbox vector layer of the current bboxFilter in use */
        // recreate drawings when fields are filled
        function getActiveBbox(selected_proj) {
          if (tllat !== null && tllon !== null && brlat !== null && brlon !== null) {
            var topLeft = [Number(tllon), Number(tllat)];
            var bottomRight = [Number(brlon), Number(brlat)];
            if (bottomRight[0] < topLeft[0]) {
              topLeft[0] -= 360;
            }
            var points = [
              [
                ol.proj.transform(topLeft, 'EPSG:4326', selected_proj),
                ol.proj.transform([bottomRight[0], topLeft[1]], 'EPSG:4326', selected_proj),
                ol.proj.transform(bottomRight, 'EPSG:4326', proj),
                ol.proj.transform([topLeft[0], bottomRight[1]], 'EPSG:4326', selected_proj),
              ]
            ];
            //Create bbox draw style
            var bboxStyle = new ol.style.Style({
              stroke: new ol.style.Stroke({
                color: 'blue',
                width: 1,
              }),
              fill: new ol.style.Fill({
                color: 'rgba(0, 0, 255, 0.1)',
              }),
            });
            // Create bbox source
            var bboxSource = new ol.source.Vector({
              projection: selected_proj,
            });
            console.log('Created bboxSource');

            //Create bbox layer
            var bboxLayer = new ol.layer.Vector({
              source: bboxSource,
              style: bboxStyle,
              visible: true,
              title: 'CurrentBbox',
              projection: selected_proj,
            });
            console.log('Created bboxLayer');
            //overviewMapControl.addLayer(bboxLayer);

            var bboxGeom = new ol.geom.Polygon(points);

            //Create a feature with polygon from current bbox
            var bboxFeature = new ol.Feature(bboxGeom);
            bboxFeature.setStyle(bboxStyle);
            bboxSource.addFeature(bboxFeature);
            console.log('Created bboxFeature');

            return bboxLayer;
          }
        }

        //Adding configured additional layers
        // if (additional_layers) {
        //   console.log("Adding additional layers");
        //   addExtraLayers(selected_proj);
        // }


        //Zoom to extent
        var zoomToExtent = function zoomToExtent() {
          console.log("ZoomToExtent function.");
          return featuresExtent;
        }
        var zoomToExtentControl = new ol.control.ZoomToExtent({
          //extent: function() { zoomToExtent() },
          extent: featuresExtent,
        });
        map.addControl(zoomToExtentControl);

        //Add LayerSwitcher control
        /*        var layerSwitcher = new ol.control.LayerSwitcher({
                  tipLabel: 'Legend', // Optional label for button
                  groupSelectStyle: 'children' // Can be 'children' [default], 'group' or 'none'
                });
                map.addControl(layerSwitcher);
        */
        // Add a layer switcher outside the map
        var switcher = new ol.control.LayerSwitcher({
          target: $(".layerSwitcher").get(0),
          // displayInLayerSwitcher: function (l) { return false; },
          show_progress: true,
          extent: true,
          trash: function (l) {
            console.log("Trash function");
            console.log(l);
            if (l.get('baseLayer') === true) {
              return false;
            }
            else {
              return true;
            }
          },
          oninfo: function (l) {
            var title = l.get('title');
            try {
              var legendUrl = l.getSource().getLegendUrl();
              //$('#bottomMapPanel').append('<img id="map-bottom-wms-legend" />');
              var img = document.getElementById('map-wms-legend');
              img.src = legendUrl;
              //$('#bottomMapPanel').show();
            } catch {
              console.log("No legendinfo");
            }
          }
        });
        switcher.on('toggle', function (e) {
          console.log(e);

        });
        map.addControl(switcher);

        //Map reset button:
        $('#resetMapButton').on("click", function (e) {
          wmsLayerGroup.getLayers().clear();
          featureLayersGroup.getLayers().clear();

          featuresExtent = buildFeatures(projObjectforCode[selected_proj].projection);
          featureLayersGroup.setVisible(true);
          map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          map.getView().fit(featuresExtent);
          map.getView().setZoom(map.getView().getZoom() - 0.3);
        });

        /** WMS LAYERS - Visualize all **/
        //Loop over the extracted info, and check how many wms resources we have
        var wmsProducts = [];
        var wmsProductLayers = [];
        var wmsLayersFromMmd = []
        for (var i = 0; i < extracted_info.length; i++) {
          id = extracted_info[i][1];
          title = extracted_info[i][4];
          wms = extracted_info[i][0][1];
          wmslayer = extracted_info[i][17];
          //if(debug) {console.log("id: "+id+ ",wms:" +wms)};
          if (wms != null && wms != "" && isSentinelProduct(title, ['S1B', 'S1A', 'S2B', 'S2A', 'S2C', 'S1C'])) {
            wmsProducts.push(title);
            wmsProductLayers.push(wms);
            if (wmslayer != null) {
              wmsLayersFromMmd.push(wmslayer);
            }
            else {
              wmsLayersFromMmd.push('NA');
            }
          }
        }

        // If we have wms datasets in map, show the visualise all button
        //list of olWMALayers to be added and rendered
        var wmsLayers = [];
        if (wmsProducts.length > 0) {
          $('#vizAllButton').css('display', 'block');
          $('#vizAllButton').append().text('Visualise all Sentinel products in Map');
          $('#vizAllButton').on("click", function (e) {
            console.log("Visualize all wms click event");
            console.log("current projection" + selected_proj);

            //Loop over the wmsLayers and render them on map.
            for (let i = 0; i < wmsProductLayers.length; i++) {
              if (debug) { console.log(i + " - " + wmsProducts[i]); }

              if (debug) { console.log("wms_layer_name_from_mmd: " + wmsLayersFromMmd[i]); }
              //alert(wmsProducts[i]);

              var wmsUrl = wmsProductLayers[i];
              wmsUrl = wmsUrl.replace(/(^\w+:|^)\/\//, '//');
              wmsUrl = wmsUrl.split("?")[0];
              var myGroup = new ol.layer.Group({
                title: wmsProducts[i],
              });
              var layer_name = 'Composites';
              if (wmsLayersFromMmd[i] == "True Color Vegetation Composite") {
                layer_name = 'true_color_vegetation';
              }
              else if (wmsLayersFromMmd[i] == "Amplitude HH polarisation") {
                layer_name = 'amplitude_hh';
              }
              else if (wmsLayersFromMmd[i] == "Amplitude HV polarisation") {
                layer_name = 'amplitude_hv';
              }
              else if (wmsLayersFromMmd[i] == "Amplitude VV polarisation") {
                layer_name = 'amplitude_vv';
              }
              else if (wmsLayersFromMmd[i] == "Amplitude VH polarisation") {
                layer_name = 'amplitude_vh';
              }
              else if (wmsLayersFromMmd[i] == "True Color Vegetation Composite") {
                layer_name = 'true_color_vegetation';
              }
              else {
                if (wmsProducts[i].startsWith('S1')) {
                  if (wmsProducts[i].includes('EW')) {
                    layer_name = "amplitude_hh";
                  }
                  if (wmsProducts[i].includes('IW')) {
                    layer_name = "amplitude_vv";
                  }
                }
                else {
                  layer_name = 'true_color_vegetation';
                }
                console.log("Fallback layer: " + layer_name)
              }
              myGroup.getLayers().push(
                //map.addLayer(
                new ol.layer.Tile({
                  title: layer_name,
                  visible: true,
                  //projection: selected_proj,
                  source: new ol.source.TileWMS(({
                    url: wmsUrl,
                    //projection: selected_proj,
                    reprojectionErrorThreshold: 0.1,
                    params: {
                      'LAYERS': layer_name,
                      'VERSION': '1.3.0',
                      'FORMAT': 'image/png',
                      'TILE': true,
                      'TRANSPARENT': true,
                    },
                    crossOrigin: 'anonymous',
                  })),
                }),
              );
              myGroup.getLayers().push(
                //map.addLayer(
                new ol.layer.Tile({
                  title: 'Composites',
                  visible: false,
                  //projection: selected_proj,
                  source: new ol.source.TileWMS(({
                    url: wmsUrl,
                    //projection: selected_proj,
                    reprojectionErrorThreshold: 0.1,
                    params: {
                      'LAYERS': 'Composites',
                      'VERSION': '1.3.0',
                      'FORMAT': 'image/png',
                      'TILE': true,
                      'TRANSPARENT': true,
                    },
                    crossOrigin: 'anonymous',
                  })),
                }),
              );
              wmsLayerGroup.getLayers().push(myGroup);
            }
            //map.getLayers().extend(wmsLayerGroup);
            //map.addLayers(wmsLayerGroup);
            featureLayersGroup.setVisible(false);
            progress_bar()
          });
          //id_tooltip_h()
        }
        /* Draw bounding box filter event */
        // Search bbox filter
        $('#bboxButton').click(function () {
          console.log('Creating bbox filter with projection: ' + proj);

          //hide layers
          featureLayersGroup.setVisible(false);
          wmsLayerGroup.setVisible(false);

          //Unset the current product overlays and mouse position control
          map.un('singleclick', getProductInfo);

          //remove mouse position control
          map.removeControl(mousePositionControl);
          //Remove overlay
          map.removeOverlay(overlayh);

          //New draw Mouseposition control
          var mousePositionControl = new ol.control.MousePosition({
            coordinateFormat: function (co) {
              return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
            },
            projection: 'EPSG:4326',
          });
          map.addControl(mousePositionControl);

          // Build the draw of bbox
          build_draw(selected_proj)
        });

        //Draw bbox function
        function build_draw(selected_proj) {

          // Add drawing vector source
          var drawingSource = new ol.source.Vector({
            projection: selected_proj
          });
          //Add drawing layer
          var drawingLayer = new ol.layer.Vector({
            source: drawingSource,
            title: 'draw',
            projection: selected_proj
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
            } else {
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
            } else if (topLeft[0] > 180) {
              topLeft[0] -= 360;
            }
            if (bottomRight[0] < -180) {
              bottomRight[0] += 360;
            } else if (bottomRight[0] > 180) {
              bottomRight[0] -= 360;
            }
            if (topLeft[0] < 0 && bottomRight[0] > 0 && bottomRight[0] - topLeft[0] > 180) {
              var topLeftCopy = topLeft[0];
              topLeft[0] = bottomRight[0];
              bottomRight[0] = topLeftCopy;
            }
            if (topLeft[0] < bottomRight[0]) {
              var topLeftCopy = topLeft[0];
              topLeft[0] = bottomRight[0];
              bottomRight[0] = topLeftCopy;
            }

            //Get the current selected filter
            var choices = [];
            $("input[name='map-filter']:checked").each(function () {
              choices.push($(this).attr('value'));
            });
            selected_filter = choices[0];
            console.log("prediacte: " + selected_filter);
            localStorage.setItem('map_bbox_op', selected_filter);
            //var flt = document.getElementsByName('map-filter');
            //console.log(flt);
            /* Populate the bbox search api exposed form filter with this bbox*/
            // Example: ENVELOPE(-10, 20, 15, 10) which is minX, maxX, maxY, minY order.
            // 'ENVELOPE(' . $tllon . ',' . $brlon . ',' . $tllat . ',' . $brlat . ')';
            // Populate the input fields.
            console.log("TopLeft");
            console.log(topLeft);
            console.log("BottomRight");
            console.log(bottomRight);

            $('input[name="bbox[minX]"][data-drupal-selector="edit-bbox-minx"]').val(bottomRight[0]);
            $('input[name="bbox[maxX]"][data-drupal-selector="edit-bbox-maxx"]').val(topLeft[0]);
            $('input[name="bbox[maxY]"][data-drupal-selector="edit-bbox-maxy"]').val(topLeft[1]);
            $('input[name="bbox[minY]"][data-drupal-selector="edit-bbox-miny"]').val(bottomRight[1]);

            $('select[name="bbox_op"][data-drupal-selector="edit-bbox-op"]').val(selected_filter.toLowerCase()).prop('selected', true);;

            if (search_view === 'metsis_search') {
              $('#views-exposed-form-metsis-search-results').submit();
            }
            if (search_view === 'metsis_simple_search') {
              //$('#views-exposed-form-metsis-simple-search-results').submit();
            }

            /* Send the bboundingbox back to drupal metsis search controller to add the current boundingbox filter to the search query */
            // var myurl = '/metsis/search/map?tllat=' + topLeft[1] + '&tllon=' + topLeft[0] + '&brlat=' + bottomRight[1] + '&brlon=' + bottomRight[0] + '&proj=' + selected_proj + '&cond=' + selected_filter;
            // console.log('calling controller url: ' + myurl);

            // data = Drupal.ajax({
            //   url: myurl,
            //   async: false
            // }).execute();

            // //Do something after ajax call are complete
            // $(document).ajaxComplete(function (event, xhr, settings) {
            //   console.log('ajax complete:' + drupalSettings.metsis_search_map_block.bboxFilter);
            //   var bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
            //   $('.current-bbox-select').text(bboxFilter);

            //   var tllat = drupalSettings.metsis_search_map_block.tllat;
            //   var tllon = drupalSettings.metsis_search_map_block.tllon;
            //   var brlat = drupalSettings.metsis_search_map_block.brlat;
            //   var brlon = drupalSettings.metsis_search_map_block.brlon;

            //   location.href = window.location.href; //Redirect

            // });
            //Create popup with search button
            //$('#popup-content').append("<p>" + feature_ids[id].title + "</p>");
            /*            console.log("Creating search button in popup content");
                        var button = $('#popup-content').append(
                          $(document.createElement('button')).prop({
                            class: "w3-button w3-small",
                          }).html('Search with current boundingbox')

                        );

                        console.log("Setting popup position after draw|.");
                        popUpOverlay.setPosition(coords);
                        button.on('click', function() {
                          window.location.replace(current_search);
                          return false;
                        });
            */
          });
          console.log('Adding draw bbox interaction');
          map.addInteraction(draw);
          /*
           tllat = drupalSettings.metsis_search_map_block.tllat;
           tllon = drupalSettings.metsis_search_map_block.tllon;
           brlat = drupalSettings.metsis_search_map_block.brlat;
           brlon = drupalSettings.metsis_search_map_block.brlon;
           */
          console.log('tllat before draw existing filter' + tllat);

          // two proj
        }
        //Add extra layers function
        function addExtraLayers(proj) {

          document.getElementById("droplayers").style.display = "none";

          if (additional_layers && (proj == 'EPSG:4326' || proj == 'EPSG:32661')) {
            $('#droplayers').appendTo(
              $('.ol-overlaycontainer-stopevent')
            );
            featureLayers['europaveg'] = new ol.layer.Tile({
              title: 'europaveg',
              source: new ol.source.TileWMS({
                url: 'https://wms.geonorge.no/skwms1/wms.vegnett2?',
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

            featureLayers['riksveg'] = new ol.layer.Tile({
              title: 'riksveg',
              displayInLayerSwitcher: true,
              source: new ol.source.TileWMS({
                url: 'https://wms.geonorge.no/skwms1/wms.vegnett2?',
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

            featureLayers['fylkesveg'] = new ol.layer.Tile({
              title: 'fylkesveg',
              source: new ol.source.TileWMS({
                url: 'https://wms.geonorge.no/skwms1/wms.vegnett2?',
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


            for (var i = layers_list.length; i--;) {
              var ald = document.getElementById("lrslist").children; //list of li
              if (ald[i].children[0].checked) {
                selectedLayer = ald[i].children[0].value;
                map.addLayer(featureLayers[selectedLayer]);
              }
              ald[i].children[0].onclick = function select_extralayer() {
                if (this.checked) {
                  selectedLayer = this.value;
                  map.addLayer(featureLayers[selectedLayer]);
                } else {
                  selectedLayer = this.value;
                  map.removeLayer(featureLayers[selectedLayer]);
                }
              }
            }

            document.getElementById("droplayers").style.display = "inline";
          }
        }

        //geonorge stedsnavn provider implementation.
        /**
        * Custom provider for OS OpenNames search covering Great Britian.
        * Factory function which returns an object with the methods getParameters
        * and handleResponse called by the Geocoder
        */
        function geoNorgeSearch(options) {
          const { url } = options;

          return {
            /**
             * Get the url, query string parameters and optional JSONP callback
             * name to be used to perform a search.
             * @param {object} options Options object with query, key, lang,
             * countrycodes and limit properties.
             * @return {object} Parameters for search request
             */
            getParameters(opt) {
              return {
                url,
                callbackName: 'callback',

                params: {
                  navn: opt.query,
                  eksakteForst: 'true',
                  json: 'json',
                  antPerSide: 10,
                },
              };
            },

            /**
             * Given the results of performing a search return an array of results
             * @param {object} data returned following a search request
             * @return {Array} Array of search results
             */
            handleResponse(results) {
              // The API returns a GeoJSON FeatureCollection
              if (results && results.totaltAntallTreff > 0) {
                return results.stedsnavn.map((feature) => {
                  return {
                    lon: feature.aust,
                    lat: feature.nord,

                    address: {
                      // Simply return a name in this case, could also return road,
                      // building, house_number, city, town, village, state,
                      // country
                      name: feature.stedsnavn,
                    },

                    //bbox: feature.bbox,
                  };
                });
              }

              return [];
            },
          };
        }
      });
    },
  };

})(jQuery, Drupal, drupalSettings, once);
