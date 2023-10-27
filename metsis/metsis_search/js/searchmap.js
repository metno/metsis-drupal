(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.metsisMapSearch = {
    attach: function (context) {
      const mapEl = $(once('#map-search', '[data-map-search]', context));
      // console.log(mapEl);
      mapEl.each(function () {
        //  $(document).ready(function() {
        var lat = drupalSettings.metsis_search.mapLat;
        var lon = drupalSettings.metsis_search.mapLon;
        var defzoom = drupalSettings.metsis_search.mapZoom;
        //var lon = Drupal.settings.lon;
        //var lat = Drupal.settings.lat;
        //var defzoom = Drupal.settings.zoom;
        var init_proj = drupalSettings.metsis_search.init_proj;
        var additional_layers = drupalSettings.metsis_search.additional_layers;
        var tllat = drupalSettings.metsis_search.tllat;
        var tllon = drupalSettings.metsis_search.tllon;
        var brlat = drupalSettings.metsis_search.brlat;
        var brlon = drupalSettings.metsis_search.brlon;
        var base_layer_wms_north = drupalSettings.metsis_search.base_layer_wms_north;
        var base_layer_wms_south = drupalSettings.metsis_search.base_layer_wms_south;
        var projections = drupalSettings.metsis_search.projections;
        var layers_list = drupalSettings.metsis_search.layers_list;
        var selected_proj = drupalSettings.metsis_search.proj;

        console.log("Start of searchmap.js script: " + additional_layers);
        console.log("Init projection: " + init_proj);
        console.log("Selected projection: " + selected_proj);
        //Set current selected projection to initial projection if not altered by user $session
        if (selected_proj == null) {
          var selected_proj = init_proj;
          var proj = init_proj;
        } else {
          var proj = selected_proj;
        }
        // Create the projections input boxes
        for (var key in projections) {
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

        // 32661
        proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
        ol.proj.proj4.register(proj4);
        var ext32661 = [-4e+06, -3e+06, 8e+06, 8e+06];
        var center32661 = [15, 70];
        var proj32661 = new ol.proj.Projection({
          code: 'EPSG:32661',
          extent: ext32661
        });

        // 32761
        proj4.defs('EPSG:32761', '+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +ellps=WGS84 +datum=WGS84 +units=m +no_defs');
        ol.proj.proj4.register(proj4);
        var ext32761 = [-8e+06, -8e+06, 12e+06, 10e+06];
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
        /*
            var ch = document.getElementsByName('map-search-projection');
            document.getElementById(init_proj).checked = true;
            for (var i = ch.length; i--;) {
              ch[i].onchange = function change_projection() {
                var prj = this.value;
                for (var j = map.getLayers().getArray().length; j > 1; j--) {
                  map.getLayers().removeAt(j - 1);
                }
                if (prj == 'EPSG:32761') {
                  map.getLayers().removeAt(0, layer['baseN']);
                  map.getLayers().insertAt(0, layer['baseS']);
                } else {
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
            selected_proj = proj;
            console.log("change projection event: " + prj);
            //Update session information with user selected projection
            /* Send the bboundingbox back to drupal metsis search controller to add the current boundingbox filter to the search query */
            var myurl = '/metsis/search/map/projection?&proj=' + selected_proj;
            console.log('calling controller url: ' + myurl);
            data = Drupal.ajax({
              url: myurl,
              async: false
            }).execute();
            //Do something after ajax call are complete
            $(document).ajaxComplete(function (event, xhr, settings) {
              console.log('ajax complete:' + drupalSettings.metsis_search.proj);
              selected_proj = drupalSettings.metsis_search_map_block.proj;
              console.log("Selected proj after projection ajax call: " + selected_proj);
            });
            //Remove pins ans polygons
            console.log("Remove pins and polygons layers");
            //featureLayersGroup.getLayers().clear();
            console.log("Update view to new selected projection: " + prj);
            //console.log("Features extent: " + featuresExtent);
            map.setView(new ol.View({
              //center: ol.extent.getCenter(featuresExtent),
              //center: ol.proj.transform([lon, lat], 'EPSG:4326', projObjectforCode[init_proj].projection),
              //extent: projObjectforCode[prj].extent,
              //projection: projObjectforCode[prj].projection,
              zoom: defzoom,
              center: ol.proj.transform([lon, lat], 'EPSG:4326', projObjectforCode[prj].projection['code_']),
              extent: projObjectforCode[prj].extent,
              projection: projObjectforCode[prj].projection['code_'],
              //projection: prj,
            }));
            map.getView().setZoom(map.getView().getZoom());
          }

        }
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
            url: base_layer_wms_south,
            params: {
              'LAYERS': 'world',
              'TRANSPARENT': 'false',
              'VERSION': '1.3.0',
              'FORMAT': 'image/png'
            },
            crossOrigin: 'anonymous'
          })
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
        //Create a layergroup to hold the different basemaps
        const baseLayerGroup = new ol.layer.Group({
          title: 'Base Layers',
          //openInLayerSwitcher: true,
          layers: [
            osmStandard
          ],
        });
        var map = new ol.Map({
          target: 'metmap',
          layers: baseLayerGroup,
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

        function build_draw(selected_proj) {
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
          var geometryFunction = function (coordinates, geometry, projection, tllat, tllon, brlat, brlon) {
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
          //var tllat = document.getElementById('edit-bbox-top-left-lat');
          //var tllon = document.getElementById('edit-bbox-top-left-lon');
          //var brlat = document.getElementById('edit-bbox-bottom-right-lat');
          //var brlon = document.getElementById('edit-bbox-bottom-right-lon');
          console.log(tllat);
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
            console.log(brlat);
            //jQuery(tllat).attr('value', topLeft[1]);
            //jQuery(tllon).attr('value', topLeft[0]);
            //jQuery(brlat).attr('value', bottomRight[1]);
            //jQuery(brlon).attr('value', bottomRight[0]);
            var myurl = '/metsis/search/map?tllat=' + topLeft[1] + '&tllon=' + topLeft[0] + '&brlat=' + bottomRight[1] + '&brlon=' + bottomRight[0] + '&proj=' + selected_proj;
            console.log('calling controller url: ' + myurl);
            Drupal.ajax({
              url: myurl,
              async: false
            }).execute();
            //});
            console.log('finished');
          });
          map.addInteraction(draw);
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
              displayInLayerSwitcher: true,
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
                } else {
                  selectedLayer = this.value;
                  map.removeLayer(layer[selectedLayer]);
                }
              }
            }
            document.getElementById("droplayers").style.display = "inline";
          }
        }
        if (additional_layers) {
          console.log("Adding additional layers");
          addExtraLayers(init_proj);
        }
        console.log("End of searchmap.js script");
      });
    },
  };
})(jQuery, Drupal, drupalSettings, once);
