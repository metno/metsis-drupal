console.log("Start of wms map script:");
(function ($, Drupal, drupalSettings, once) {

  console.log("Attaching WMS map script to drupal behaviours:");
  /** Attach the metsis map to drupal behaviours function */
  Drupal.behaviors.metsisWmsMap = {
    attach: function (context) {
      const mapEl = $(once('#search-map', '[data-search-map]', context));
      mapEl.each(function () {
        //$('#map-res', context).once('metsisSearchBlock').each(function() {
        /** Start reading drupalSettings sent from the mapblock build */
        console.log('Initializing METSIS WMS Map...');

        //Default Zoom value
        var defzoom = 4;

        // Import variables from drupalSettings send by block build array
        var path = drupalSettings.metsis_wms_map.path;
        var site_name = drupalSettings.metsis_wms_map.site_name;

        var lat = drupalSettings.metsis_wms_map.mapLat;
        var lon = drupalSettings.metsis_wms_map.mapLon;
        var mapZoom = drupalSettings.metsis_wms_map.mapZoom;

        var init_proj = drupalSettings.metsis_wms_map.init_proj;
        var projections = drupalSettings.metsis_wms_map.projections;
        var layers_list = drupalSettings.metsis_wms_map.layers_list;
        var additional_layers = drupalSettings.metsis_wms_map.additional_layers;
        var pywpsUrl = drupalSettings.metsis_wms_map.pywps_service;
        var wms_layers_skip = drupalSettings.metsis_wms_map.wms_layers_skip;
        var wms_data = drupalSettings.metsis_wms_map.wms_data;
        var ups_north_proj = drupalSettings.metsis_wms_map.ups_north_proj ? true : false;
        let selected_proj = drupalSettings.metsis_wms_map.selected_proj;
        // Some debugging
        var debug = true;
        if (debug) {
          console.log("Reading drupalSettings: ")
          console.log('show additional layers: ' + additional_layers);
          console.log('init proj: ' + init_proj);
          console.log('initial map zoom: ' + mapZoom);
          console.log('current selected proj: ' + selected_proj);
          console.log("WMS Layers to skip: ");
          console.log(wms_layers_skip);
          console.log("Registerd projections: ");
          console.log(projections);
          console.log("Use mapserver 8.x proj? " + ups_north_proj);

        }
        console.log("Wms data is:");
        console.log(wms_data);

        //Set the configured zoom level as the same as default:
        defZoom = mapZoom;
        //Set current selected projection to initial projection if not altered by user $session
        var proj = init_proj;
        //var selected_proj = null;
        if (selected_proj == null) {
          selected_proj = init_proj;
          proj = init_proj;
          console.log("Set selected proj to: " + selected_proj);
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

        /**
         * Define the proj4 map_projections
         */
        // proj4.defs("EPSG:32661", "+proj=stere +lat_0=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs +type=crs +axis=neu");
        if (ups_north_proj == true) {
          proj4.defs("EPSG:32661", 'PROJCS["WGS 84 / UPS North (N,E)",GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0,AUTHORITY["EPSG","8901"]],UNIT["degree",0.0174532925199433,AUTHORITY["EPSG","9122"]],AUTHORITY["EPSG","4326"]],PROJECTION["Polar_Stereographic"],PARAMETER["latitude_of_origin",90],PARAMETER["central_meridian",0],PARAMETER["scale_factor",0.994],PARAMETER["false_easting",2000000],PARAMETER["false_northing",2000000],UNIT["metre",1,AUTHORITY["EPSG","9001"]],AXIS["Northing",SOUTH],AXIS["Easting",SOUTH],AUTHORITY["EPSG","32661"]]');
        }
        else {
          proj4.defs("EPSG:32661", "+proj=stere +lat_0=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs +type=crs");
        }

        proj4.defs("EPSG:32761", "+proj=stere +lat_0=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs +type=crs");
        proj4.defs("EPSG:5041", "+proj=stere +lat_0=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs +type=crs");
        proj4.defs("EPSG:4326", "+proj=longlat +datum=WGS84 +no_defs +type=crs");
        ol.proj.proj4.register(proj4);

        // 32661
        var ext32661 = [-6e+06, -3e+06, 9e+06, 6e+06];
        var center32661 = [0, 80];
        var proj32661 = new ol.proj.Projection({
          code: 'EPSG:32661',
          extent: ext32661
        });
        console.log("Debug 32661");
        console.log(proj32661);

        // 32761
        var ext32761 = [-8e+06, -8e+06, 12e+06, 10e+06];
        var center32761 = [0, -90];
        var proj32761 = new ol.proj.Projection({
          code: 'EPSG:32761',
          extent: ext32761
        });

        // 5041
        var ext5041 = [-8e+06, -8e+06, 12e+06, 10e+06];
        var center5041 = [0, -90];
        var proj5041 = new ol.proj.Projection({
          code: 'EPSG:5041',
          extent: ext5041
        });

        // 4326
        var ext4326 = [-350.0000, -100.0000, 350.0000, 100.0000];
        var center4326 = [0, 0];
        //var proj4326 = new ol.proj.Projection('EPSG:4326');
        var proj4326 = new ol.proj.Projection({
          code: 'EPSG:4326',
          extent: ext4326
        });
        console.log("Defined extension for this proj: " + proj4326.getExtent());
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
          },
          'EPSG:5041': {
            extent: ext5041,
            center: center5041,
            projection: proj5041
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
            selected_proj = this.value;
            console.log("change projection event: " + selected_proj);

            console.log("Update view to new selected projection: " + projObjectforCode[selected_proj].projection.getCode());
            console.log("Axis orientation is: " + projObjectforCode[selected_proj].projection.getAxisOrientation())
            //console.log("Features extent: " + featuresExtent);
            map.setView(new ol.View({
              //center: ol.extent.getCenter(featuresExtent),
              zoom: 3,
              //minZoom: 0,
              //maxZoom: 23,
              center: projObjectforCode[selected_proj].center,
              extent: projObjectforCode[selected_proj].extent,
              projection: projObjectforCode[selected_proj].projection.getCode(),
              //projection: selected_proj,
            }));
            /*        map.getView().setZoom(map.getView().getZoom());*/
            wmsLayerGroup.getLayers().forEach(function (layer, index, array) {
              if (layer instanceof ol.layer.Tile) {
                // layer.getSource().setProperties({ 'projection': selected_proj });
                layer.getSource().refresh();
              }
              else {
                layer.getLayers().forEach(function (layer, index, array) {
                  if (layer instanceof ol.layer.Tile) {
                    // layer.getSource().setProperties({ 'projection': selected_proj });
                    layer.getSource().refresh();

                  }
                });
              }
            });
            //progress_bar()
            map.getView().setZoom(map.getView().getZoom());
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
            osmStandard, osmHumanitarian, stamenTerrain, esriSatellite
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
        var wmsStyles = []
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
        var ovMapLayers = [];
        var ovBaseLayer = new ol.layer.Tile({
          //baseLayer: true,
          visible: true,
          source: new ol.source.OSM(),
          projection: selected_proj,
        });
        ovMapLayers.push(ovBaseLayer);

        //Add MapControls

        //Add OverVoewMapControl
        var ovMapControl = new ol.control.OverviewMap({
          //className: 'ol-overviewmap bboxViewMap',
          title: 'overviewMap',
          layers: ovMapLayers,
          collapsed: true,
        });

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
            //map.getView().setCenter(ol.extent.getCenter(featuresExtent));
            //map.getView().fit(featuresExtent, { size: map.getSize() });
            //map.getView().fit(featuresExtent);
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
            //map.getView().setCenter(ol.extent.getCenter(featuresExtent));
            //map.getView().fit(featuresExtent, { size: map.getSize() });
            //map.getView().fit(featuresExtent);
            map.getView().setZoom(map.getView().getZoom());
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

            // Control.init({
            //   element: element,
            //   target: options.target,
            // });
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
              //map.getView().setCenter(ol.extent.getCenter(featuresExtent));
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
            controls: ol.control.defaults().extend([ovMapControl, sideBarControl, fullScreenControl, scaleLineControl, mousePositionControl]),
            //controls: ol.control.defaults().extend([fullScreenControl]),
            //layers: [baseLayerGroup,featureLayersGroup],
            layers: [baseLayerGroup, wmsLayerGroup],
            //overlays: [overlayh, popUpOverlay],
            view: new ol.View({
              zoom: 2,
              minZoom: 0,
              maxZoom: 23,
              //rotation: 0.5,
              center: projObjectforCode[selected_proj].center,
              extent: projObjectforCode[selected_proj].extent,
              projection: projObjectforCode[selected_proj].projection.getCode(),
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
          //map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          //map.getView().fit(featuresExtent);
          map.getView().setZoom(map.getView().getZoom());
          //console.log(map.getLayers()[0]);
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
        //Function for retrieving wms capabilities
        function getWmsLayers(wmsUrl, title, geom) {
          if (wmsUrl != null && wmsUrl != "") {
            //console.log("Got wms resource: " +wmsUrl);
            //console.log("Parsing getCapabilties");
            var getCapString = '?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities';
            var parser = new ol.format.WMSCapabilities();
            var hasTimeDimension = false;
            var defaultTime = null;
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
                              if (times.length == 1 && dimensions[j].values.indexOf('/')) {
                                console.log("wms1: got timerange");
                              }
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
            console.log("Got WMS URL: " + wmsUrl)
            //Do ajax call.
            //   fetch(wmsUrl+getCapString,{
            //      mode: 'cors',
            //    }).then(function(response) {
            wmsUrl.replace('http://', 'https://');
            wmsUrl = wmsUrl.replace(/(^\w+:|^)\/\//, '//');
            wmsUrl = wmsUrl.replace('//lustre', '/lustre');
            if (wmsUrl.includes('wms.wps.met.no/get_wms')) {
              wmsUrlOrig = wmsUrl;
            }
            else if (wmsUrl.includes('mapserver.wps.met.no')) {

              wmsUrl = wmsUrlOrig + '&service=WMS&request=GetCapabilities&version=1.3.0';
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
                if (ls === undefined) {
                  lst = layers[idx]
                  if (lst !== undefined) {
                    ls = [lst];
                  }

                }
                if (ls !== undefined) {
                  console.log("Got layer: " + ls.Name);
                  for (let i = 0; i < ls.length; i++) {
                    var getTimeDimensions = function () {
                      var dimensions = ls[i].Dimension;
                      if (ls[i].Dimension) {
                        for (var j = 0; j < dimensions.length; j++) {
                          if ("time" === dimensions[j].name.toLowerCase()) {
                            var times = dimensions[j].values.split(",");
                            if (times.length == 1 && dimensions[j].values.indexOf('/')) {
                              var startDate = dimensions[j].values.split("/")[0];
                              var endDate = dimensions[j].values.split("/")[1];
                              var duration = dimensions[j].values.split("/")[2];


                              _defaultTimeDim = dimensions[j].default;

                              //console.log("wms2: got timerange. default: " + _defaultTimeDim);
                              //console.log("start: " + startDate);
                              //console.log("end: " + endDate);
                              //console.log("duration: " + duration);


                              if (_defaultTimeDim !== undefined) {
                                defaultTimeDim = _defaultTimeDim;
                                console.log("timedim default: " + defaultTimeDim);
                              }
                              if (startDate === endDate) {
                                times = [startDate];
                              }
                              else if (endDate === undefined && duration === undefined) {
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
                          if ("elevation" === dimensions[j].name.toLowerCase() || "depth" === dimensions[j].name.toLowerCase()) {
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
                    visible = (i === 0) ? true : false;
                    console.log("i=" + idx + " layer_name: " + ls[i].Name);
                    if (wmsLayerMmd.length > 0) {
                      console.log("Got wms layers from MMD. Loading only those provided");
                      if (($.inArray(ls[i].Name, wms_layers_skip) === -1) &&
                        (($.inArray(ls[i].Name, wmsLayerMmd) !== -1) ||
                          ($.inArray(ls[i].Title, wmsLayerMmd) !== -1))) // ||
                      //(($.inArray(ls[i].Title, wmsLayerMmd) === -1) ||
                      // ($.inArray(ls[i].Title, wmsLayerMmd) === -1)))
                      {
                        //visible = (idx === 0) ? true : false;
                        wmsGroup.getLayers().insertAt(i,
                          new ol.layer.Tile({
                            title: title,
                            visible: false,
                            //extent: extent,

                            //keepVisible: false,
                            //preload: 5,
                            //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                            dimensions: getTimeDimensions(),
                            styles: ls[i].Style,
                            source: new ol.source.TileWMS(({
                              url: wmsUrl,
                              reprojectionErrorThreshold: 0.1,
                              //projection: selected_proj,
                              hidpi: false,
                              params: {
                                'TILED': true,
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
                    else {
                      console.log("No given mmd layers. Loading all");
                      if ($.inArray(ls[i].Name, wms_layers_skip) === -1) {
                        wmsGroup.getLayers().insertAt(i,
                          new ol.layer.Tile({
                            title: title,
                            visible: false,
                            //extent: extent,

                            //keepVisible: false,
                            //preload: 5,
                            //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                            dimensions: getTimeDimensions(),
                            styles: ls[i].Style,
                            source: new ol.source.TileWMS(({
                              url: wmsUrl,
                              reprojectionErrorThreshold: 0.1,
                              hidpi: false,
                              //projection: selected_proj,
                              params: {
                                'TILED': true,
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
                  }

                  //Update timedimension variables for animation
                  //hasTimeDimension = false;

                }

              }
              //})
              wmsGroup.getLayers().item(0).setVisible(true);
              wmsGroup.getLayers().getArray().reverse();

              wmsLayerGroup.getLayers().push(wmsGroup);
              //wmsLayerGroup.set('title', productTitle, false);
              featureLayersGroup.setVisible(false);
              // Add controls for wms style change
              //$('#wms-styles-select').change(function () {
              const wmsSelect = document.getElementById("wms-styles-select");
              wmsSelect.addEventListener('change', function handleChange(event) {
                //wmsLayerGroup.setOpacity(ui.value / 100);
                console.log("Selected style: " + event.target.value);
                const selected_style = event.target.value;
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
                if (bbox != undefined) {
                  geom = extent = ol.proj.transformExtent(bbox, 'EPSG:4326', selected_proj);
                  map.getView().fit(geom);
                }

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
                dataType: 'xml',
                //xhrFields: { withCredentials: true },
                // headers: { "Access-Control-Allow-Origin": '*' },
                crossDomain: true,
                //async: false,
                error: function () {
                  console.log("Request failed: " + proxyURL + wmsUrlOrig);

                },
                success: function (response) {
                  console.log("Proxy getcap success");
                  //wmsURL = proxyURL + wmsUrlOrig;
                  onGetCapSuccess(response)
                },
              });
            }
            if (wmsUrl.includes('wms.wps.met.no/get_wms')) {
              $.ajax({
                type: 'GET',
                url: wmsUrl,
                dataType: 'xml',
                // xhrFields: { withCredentials: true },
                // headers: { "Access-Control-Allow-Origin": '*' },
                crossDomain: true,
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

            else if (wmsUrl.includes('mapserver.wps.met.no')) {
              console.log("Special mapserver test url");
              console.log(wmsUrl);
              wmsUrlOrig = wmsUrl;
              $.ajax({
                type: 'GET',
                url: wmsUrl,
                dataType: 'xml',
                // xhrFields: { withCredentials: true },
                // headers: { "Access-Control-Allow-Origin": '*' },
                crossDomain: true,
                //async: false,
                error: function () {
                  console.log("Request failed: " + wmsUrl);
                  console.log("Trying getCapProxy.... " + wmsUrlOrig);
                  tryProxy(proxyURL, wmsUrlOrig)
                },
                success: function (response) {

                  onGetCapSuccess(response)
                },
              });
            }
            else if (wmsUrl.includes('thredds.nersc.no')) {
              console.log("Special handeling thredds.nersc.no");
              console.log(wmsUrl);
              wmsUrlOrig = wmsUrl;
              $.ajax({
                type: 'GET',
                url: wmsUrl,
                dataType: 'xml',
                // xhrFields: { withCredentials: true },
                headers: { "AccOrigin": '‘https://blueinsight.io' },
                crossDomain: true,
                //async: false,
                error: function () {
                  console.log("Request failed: " + wmsUrl);
                  console.log("Trying getCapProxy.... " + wmsUrlOrig);
                  tryProxy(proxyURL, wmsUrlOrig)
                },
                success: function (response) {

                  onGetCapSuccess(response)
                },
              });
            }
            else {
              console.log("Else: " + wmsUrl);
              $.ajax({
                type: 'GET',
                url: wmsUrl + getCapString,
                dataType: 'xml',
                crossDomain: true,
                // xhrFields: { withCredentials: true },
                // headers: { "Access-Control-Allow-Origin": '*' },
                //async: false,
                error: function () {
                  console.log("Request failed: " + wmsUrl + getCapString);
                  console.log("Trying getCapProxy....");
                  if (wmsUrl.includes('thredds.nersc')) {
                    let _url = wmsUrlOrig.replace('http://', 'https://');
                    console.log("tryproxy nersc: " + _url + getCapString);
                    tryProxy(proxyURL, _url + getCapString)
                  }
                  else {
                    tryProxy(proxyURL, wmsUrlOrig)
                  }
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

          console.log(wmsLayerGroup.getLayers().getArray().length);
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
            //alert(newId);
            $('.datasets-' + newId).css('display', 'block');
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
              if (feature_ids[id].featureType === 'timeSeries' || feature_ids[id].featureType === 'profile') {
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
                  plotTimeseries(odResource)
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
              if (isSentinelProduct(id, sentinelStrings)) {
                //visualiseWmsLayer(wmsResource, id, title, feature_ids[id].geom, wmsLayer);
                //getWmsLayers2(wmsResource, title, feature_ids[id].geom)
              }
              /* Else we call function that add all layers and timedimensions */
              else {
                //getWmsLayers2(wmsResource, title, feature_ids[id].geom, wmsLayer)
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
                    if (isSentinelProduct(id, sentinelStrings)) {
                      //visualiseWmsLayer(wmsResource, selected_id, title, feature_ids[selected_id].geom, feature_ids[id].wms_layer)
                    }
                    /* Else we call function that add all layers and timedimensions */
                    else {
                      //getWmsLayers2(wmsResource, title, feature_ids[selected_id].geom, feature_ids[id].wms_layer)
                    }
                  });

                }

                //Check for timeseries product
                if (odResource != null && odResource != "") {
                  if (feature_ids[selected_id].featureType === 'timeSeries' || feature_ids[selected_id].featureType === 'profile') {
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
                      plotTimeseries(odResource)
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

          //map.on('click', tooltipclick);
        }

        function id_tooltip_new() {
          //var tooltip = document.getElementById('tlp-map-res');
          console.log('Register product select event');

          //map.on('singleclick', getProductInfo);
        }

        //build up the point/polygon features
        function buildFeatures(prj) {
          console.log("Building polygons and pins features....");
          //console.log(prj);
          var allFeatures = [];
          var iconFeaturesPol = [];
          var iconFeaturesPin = [];
          var wmsProducts = [];
          for (var i12 = 0; i12 <= extracted_info.length - 1; i12++) {
            if (!Array.isArray(extracted_info[i12][2])) {
              geom = undefined;
            }
            //If we have a geographic extent, create polygon feature
            else if ((extracted_info[i12][2][0] !== extracted_info[i12][2][1]) || (extracted_info[i12][2][2] !== extracted_info[i12][2][3])) {
              //Transform boundingbox to selected projection and create a polygon geometry
              box_tl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][0]], 'EPSG:4326', prj);
              box_tr = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][0]], 'EPSG:4326', prj);
              box_bl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][1]], 'EPSG:4326', prj);
              box_br = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][1]], 'EPSG:4326', prj);
              geom = new ol.geom.Polygon([
                [box_tl, box_tr, box_br, box_bl, box_tl]
              ]);

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
                thumb: extracted_info[i12][7],
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
                thumb: extracted_info[i12][7],
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
          });
          var pinsFeatureLayer = new ol.layer.Vector({
            title: 'Pins',
            name: 'pinsLayer',
            visible: true,
            source: vectorSourcePin,
            //projection: prj,
            //style: iconStyle,
          });
          //create a vector layer with all points from the vector source and pins

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
        // display clickable ID in tooltip
        //console.log('calling id_tooltip');
        //id_tooltip()

        //Register the tooltip and prosuct select actions
        //id_tooltip_new()
        //id_tooltip_h()
        init()
        progress_bar()
        $('#map-sidepanel').hide();
        $('#bottomMapPanel').hide();

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


        //Adding configured additional layers
        if (additional_layers) {
          console.log("Adding additional layers");
          addExtraLayers(selected_proj);
        }


        //Zoom to extent
        /*
        var zoomToExtentControl = new ol.control.ZoomToExtent({
          //extent: function() { zoomToExtent() },
          extent: featuresExtent,
        });
        map.addControl(zoomToExtentControl);
        */

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
              var res = map.getView().getResolution();
              var legendUrl = l.getSource().getLegendUrl(res);
              console.log("Got legend url: " + legendUrl);
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
        switcher.on('change:visible', function (e) {
          console.log(e);

        });
        map.addControl(switcher);


        //Map reset button:
        $('#resetMapButton').on("click", function (e) {
          //wmsLayerGroup.getLayers().clear();
          //featureLayersGroup.getLayers().clear();

          //featuresExtent = buildFeatures(projObjectforCode[selected_proj].projection);
          //featureLayersGroup.setVisible(true);
          //map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          //map.getView().fit(featuresExtent);
          map.getView().setZoom(map.getView().getZoom() - 0.3);
        });

        /** WMS LAYERS - Visualize all **/
        //Loop over the extracted info, and check how many wms resources we have
        var wmsProducts = [];
        var wmsProductLayers = [];
        var wmsLayersFromMmd = []
        /*    for (var i = 0; i < extracted_info.length; i++) {
              id = extracted_info[i][1];
              wms = extracted_info[i][0][1];
              wmslayer = extracted_info[i][17];
              //if(debug) {console.log("id: "+id+ ",wms:" +wms)};
              if (wms != null && wms != "" && isSentinelProduct(id, ['S1B', 'S1A', 'S2B', 'S2A'])) {
                wmsProducts.push(id);
                wmsProductLayers.push(wms);
                if(wmslayer != null) {
                  wmsLayersFromMmd.push(wmslayer);
                }
                else {
                  wmsLayersFromMmd.push('NA');
                }
              }
            }

        */
        //LOOP WMS ARRAY
        var wmsUrls = [];
        var layers = [];
        var titles = [];
        var ids = [];
        var geoms = [];
        Object.keys(wms_data).forEach(key => {

          console.log(`${key} : ${wms_data[key]}`);
          var id = key;
          ids.push(id);
          Object.keys(wms_data[key]).forEach(key2 => {
            if (key2 === 'dar') {
              var wmsUrl = wms_data[key][key2];
              wmsUrls.push(wmsUrl);
            }
            if (key2 === 'layers') {
              //console.log(`${key2} : ${wms_data[key][key2]}`);
              var layer = wms_data[key][key2];
              layers.push(layer);
            }
            if (key2 === 'geom') {
              //console.log(`${key2} : ${wms_data[key][key2]}`);
              var bbox = wms_data[key][key2];
              box_tl = ol.proj.transform([bbox[3], bbox[0]], 'EPSG:4326', selected_proj);
              box_tr = ol.proj.transform([bbox[2], bbox[0]], 'EPSG:4326', selected_proj);
              box_bl = ol.proj.transform([bbox[3], bbox[1]], 'EPSG:4326', selected_proj);
              box_br = ol.proj.transform([bbox[2], bbox[1]], 'EPSG:4326', selected_proj);
              let geom = new ol.geom.Polygon([
                [box_tl, box_tr, box_br, box_bl, box_tl]
              ]);
              geoms.push(geom);
            }
            if (key2 === 'title') {
              var title = wms_data[key][key2];
              titles.push(title);
            }

          });
        });

        console.log("Gathered information");
        console.log(ids);
        console.log(wmsUrls);
        console.log(layers);
        for (let i = 0; i < ids.length; i++) {
          console.log("Looping resources: i=" + i);
          console.log(ids[i]);
          console.log(wmsUrls[i]);
          if (layers.length === 0) {
            layers.push([]);
            console.log(layers[i].length);
          }
          if (layers[i].length === 1 && layers[i][0] === 'mmd:wms_layer') {
            layers[i].pop();
          }
          console.log(layers[i]);

          //console.log(geoms[i]);
          getWmsLayers2(wmsUrls[i][0], titles[i], geoms[i], layers[i]);
          //  map.getView().setZoom(map.getView().getZoom());
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
              var myGroup = new ol.layer.Group({
                title: titles[i],
              });
              var layer_name = 'Composites';
              if (wmsResource.includes("S2")) {
                layer_name = 'true_color_vegetation';
              }
              else if (wmsLayersFromMmd[i] === "Amplitude HH polarisation") {
                layer_name = 'amplitude_hh';
              }
              else if (wmsLayersFromMmd[i] === "Amplitude HV polarisation") {
                layer_name = 'amplitude_hv';
              }
              else if (wmsLayersFromMmd[i] === "Amplitude VV polarisation") {
                layer_name = 'amplitude_vv';
              }
              else if (wmsLayersFromMmd[i] === "Amplitude VH polarisation") {
                layer_name = 'amplitude_vh';
              }
              else if (wmsLayersFromMmd[i] === "True Color Vegetation Composite") {
                layer_name = 'true_color_vegetation';
              }
              else {
                layer_name = 'Composites';
              }
              myGroup.getLayers().push(
                //map.addLayer(
                new ol.layer.Tile({
                  title: layer_name,
                  visible: true,
                  //projection: selected_proj,
                  source: new ol.source.TileWMS(({
                    url: wmsProductLayers[i],
                    //projection: selected_proj,
                    reprojectionErrorThreshold: 0.1,
                    params: {
                      'LAYERS': layer_name,
                      //'LAYERS': 'WMS',
                      //'FORMAT': 'image/jpeg',
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
                    url: wmsProductLayers[i],
                    //projection: selected_proj,
                    reprojectionErrorThreshold: 0.1,
                    params: {
                      'LAYERS': 'Composites',
                      //'LAYERS': 'WMS',
                      //'FORMAT': 'image/jpeg',
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
        document.getElementById("goBackMapButton").addEventListener("click", () => {
          history.back();
        })
      });
    },
  };

})(jQuery, Drupal, drupalSettings, once);
