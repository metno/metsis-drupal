<?php

namespace Drupal\metsis_search\Form;

/**
 * @file
 * Contains \Drupal\metsis_search\Form\MetsisMapSearchForm.
 *
 * Form to show and manipulate the Plot.
 */

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class for the Search Map form.
 *
 * {@inheritdoc}
 */
class MetsisMapSearchForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * {@inheritdoc}
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'metsis_search_map_form';
  }

  /**
   * Build the map search form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the bounding box drawn on the map.
    $this->getLogger('metsis_search')->debug("Building MapSearchForm");
    /* $tempstore = \Drupal::service('tempstore.private')->get('metsis_search');
    $bboxFilter = $tempstore->get('bboxFilter');
    $tllat = "";
    $tllon = "";
    $brlat = "";
    $brlon = "";
    if ($bboxFilter != null) {
    $tllat = $tempstore->get('tllat');
    $tllon = $tempstore->get('tllon');
    $brlat = $tempstore->get('brlat');
    $brlon = $tempstore->get('brlon');
    }
     */
    /*
     * Create the form render array
     */
    /*
     * // search-map wrapper
     * $form['search-map'] = [
     * '#prefix' => '<div id="map-search" class="map_search">',
     * '#suffix' => '</div>'
     * ];
     */
    // Projections radio buttons.
    $form['projections'] = [
      '#type' => 'radios',
      '#options' => $map_projections,
      '#default_value' => $map_init_proj,
      '#attributes' => [
        'class' => ['inline'],
      ],
    ];
    /*
     * //Alter the id and name attributes for the projection radio buttons
     * $form['search-map']['projections']['EPSG:4326'] = [
     * '#attributes' => [
     * 'id' => 'EPSG:4326',
     * 'name' => 'map-search-projection',
     * ],
     * ];
     * $form['search-map']['projections']['EPSG:32661'] = [
     * '#attributes' => [
     * 'id' => 'EPSG:32661',
     * 'name' => 'map-search-projection',
     * ],
     * ];
     * $form['search-map']['projections']['EPSG:32761'] = [
     * '#attributes' => [
     * 'id' => 'EPSG:32761',
     * 'name' => 'map-search-projection',
     * ],
     * ];
     * // Droplayers wrapper
     * $form['search-map']['droplayers'] = [
     * '#prefix' => '<div id="droplayers">',
     * '#suffix' => '</div>'
     *
     * //Droplayers button
     * $form['search-map']['droplayers']['button'] = [
     * '#type' => 'button',
     * '#value' => t('Layers'),
     * '#attributes' => [
     * 'onClick' => "document.getElementById('lrs').classList.toggle('show')",
     * 'class' => ['layers-button'],
     * 'type' => 'button',
     * ],
     * '#ajax' => [],
     * ];
     * //Droplayers list
     * $form['search-map']['droplayers']['list'] = [
     * '#prefix' => '<div id="lrs" class="panel dropdown-lrs-content">',
     * '#theme' => 'item_list',
     * '#list_type' => 'ul',
     * '#items' => $map_layers_list,
     * '#attributes' => [
     * 'id' => 'lrslist',
     * ],
     * '#suffix' => '</div>',
     *
     * //Message to be displayed under the map
    $form['search-map']['message'] = [
    #type' => 'markup',
    '#markup' => $map_search_text,

    /* $form['suffix'] = [
    '#suffix' => '</div>'
    ];
     */

    // Set the cache for this form.
    /*    $form['#cache'] = [
    'contexts' => [
    'url.path',
    'url.query_args',
    ],
    ];*/
    /*
     * // Add CSS and JS libraries and drupalSettings JS variables
     * $form['#attached'] = [
     * 'library' => [
     * 'metsis_search/search_map'
     * ],
     * 'drupalSettings' => [
     * 'metsis_search' => [
     * 'mapLat' => $map_lat, //to be replaced with configuration variables
     * 'mapLon' => $map_lon, //to be replaced with configuration variables
     * 'mapZoom' => $map_zoom, //to be replaced with configuration variables
     * 'init_proj' => $map_init_proj, //to be replaced with conf variables
     * 'additional_layers' => false, //to be replaced with config variables
     * 'base_layer_wms_north' => $map_base_layer_wms_north,
     * 'base_layer_wms_south' => $map_base_layer_wms_south,
     * 'tllat' => $tllat,
     * 'tllon' => $tllon,
     * 'brlon' => $brlon,
     * 'brlat' => $brlat,
     * ],
     * ],
     */
    // Set the id of the form.
    /* $form['#attributes'] = [
    'id' => 'map-search',
    ];
     */

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Impletment form validation here.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
     * We use ajax on this form, so this function is empty
     */
  }

  /**
   * Ajax callback function.
   */
  public function getPlotData(array $form, FormStateInterface $form_state) {
    /* tate->getValue('y_axis'));
    //Get data resource url from tempstore
    $tempstore = \Drupal::service('tempstore.private')->get('metsis_ts_bokeh');
    $data_uri = $tempstore->get('data_uri');

    //Get plot json data
    $items = adc_get_ts_bokeh_plot($data_uri, $form_state->getValue('y_axis'));
    //Create ajax response and add javascript
    $response = new AjaxResponse();
    $response->addCommand(
    new HtmlCommand(
    '.plot-container',
    );
    return $response;
     */
  }

}
