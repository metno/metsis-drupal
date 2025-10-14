// CustomResMap.js
// ES6 class extending ol/Map for custom resource mapping with projection switching and WMS support

import Map from 'ol/Map';
import View from 'ol/View';
import { Tile as TileLayer, Vector as VectorLayer } from 'ol/layer';
import { OSM, TileWMS, Vector as VectorSource } from 'ol/source';
import GeoJSON from 'ol/format/GeoJSON';
import { fromLonLat, get as getProjection } from 'ol/proj';
import { defaults as defaultControls } from 'ol/control';

export default class CustomResMap extends Map {
  constructor({ target, projections, initialProjection, center, zoom }) {
    // projections: { [code]: {label, options} }
    // initialProjection: EPSG code string
    // center: [lon, lat] in EPSG:4326
    // zoom: number

    const proj = projections[initialProjection] || Object.values(projections)[0];
    const view = new View({
      projection: getProjection(initialProjection),
      center: fromLonLat(center, initialProjection),
      zoom: zoom || 2,
    });

    super({
      target,
      layers: [
        new TileLayer({ source: new OSM() })
      ],
      view,
      controls: defaultControls(),
    });

    this.projections = projections;
    this.currentProjection = initialProjection;
    this.vectorLayer = new VectorLayer({
      source: new VectorSource(),
    });
    this.addLayer(this.vectorLayer);
  }

  switchProjection(epsgCode) {
    if (!this.projections[epsgCode]) return;
    const view = new View({
      projection: getProjection(epsgCode),
      center: fromLonLat([0, 0], epsgCode),
      zoom: this.getView().getZoom(),
    });
    this.setView(view);
    this.currentProjection = epsgCode;
  }

  loadGeoJSON(geojson) {
    // geojson: object or string
    const format = new GeoJSON();
    const features = format.readFeatures(geojson, {
      featureProjection: this.currentProjection
    });
    this.vectorLayer.getSource().clear();
    this.vectorLayer.getSource().addFeatures(features);
    // Optionally add WMS layers if features have wms property
    features.forEach(f => {
      const wmsUrl = f.get('wms');
      if (wmsUrl) {
        this.addWMSLayer(wmsUrl);
      }
    });
  }

  addWMSLayer(url, params = { LAYERS: '', TILED: true }) {
    // Remove previous WMS layers if needed
    this.getLayers().forEach(layer => {
      if (layer instanceof TileLayer && layer.get('isWMS')) {
        this.removeLayer(layer);
      }
    });
    const wmsLayer = new TileLayer({
      source: new TileWMS({
        url,
        params,
        serverType: 'geoserver',
        transition: 0,
      }),
    });
    wmsLayer.set('isWMS', true);
    this.addLayer(wmsLayer);
  }
}
