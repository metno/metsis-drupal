<?php

namespace Drupal\metsis_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metsis_search\SearchUtils;

/**
 * Class ConfigurationForm.
 *
 * {@inheritdoc}
 */
class MetsisSearchConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metsis_search.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_search.admin_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_search.settings');
    // $form = array();
    $form['note'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="messages-list__item messages messages--warning">',
      '#markup' => '<div class="messages__header"><h2 class="messages__title">Note<h2></div><div class="messages__content"> Caches might need to be rebuild before these configuration changes takes place.</div>',
      '#suffix' => '</div>',
      '#allowed_tags' => ['div', 'span', 'strong', 'h2'],
    ];
    // Get a list of collections.
    $collections = SearchUtils::getCollections();
    // dpm($collections);
    $form['collections'] = [
      '#title' => $this->t('Select which collections to include in search (if none are selected, all collections will be included in the search)'),
      '#type' => 'select',
    // '#header' => ['Collection'],
      '#options' => $collections,
      '#multiple' => TRUE,
      '#default_value' => $config->get('selected_collections'),
    ];
    /*
    $form['lp_button_var'] = [
    '#type' => 'select',
    '#title' => t('Select variable for Landing Page button text'),
    //  '#description' => t("Show pins on map or not."),
    '#options' => [
    'title' => t('Title'),
    'metadata_identifier' => t('Metadata Identifier'),
    ],
    '#default_value' => $config->get('lp_button_var'),
    ];
    $form['ts_server_type'] = [
    '#type' => 'select',
    '#title' => t('Select TimeSeries service backend'),
    //  '#description' => t("Show pins on map or not."),
    '#options' => [
    'pywps' => t('pywps'),
    'zoo' => t('zoo'),
    ],
    '#default_value' => $config->get('ts_server_type'),
    ];
     */

    $form['score_parent'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tick this box to sort parent datasets with children first in the search results.'),
      '#default_value' => $config->get('score_parent'),
      '#return_value' => TRUE,
    ];
    /*
    $form['keep_parent_filter'] = [
    '#type' => 'checkbox',
    '#title' => $this->t('Tick this box to keep the parent dataset filter when resetting the search.'),
    '#default_value' => $config->get('keep_parent_filter'),
    '#return_value' => true,
    ];
     */
    $form['ts_pywps_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter URL of TS plot service'),
    // '#description' => t("the button text for HTTP access "),
      '#size' => 100,
      '#default_value' => $config->get('pywps_service'),
    ];
    // Hide add to basket button.
    $form['hide_add_to_basket'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tick this box to hide the "add to basket"-button on search results page'),
      '#description' => $this->t("Disable the add to basket functionality in the search results"),
      '#default_value' => $config->get('hide_add_to_basket'),
    ];

    // Hide children filter checkbox.
    $form['disable_children_filter'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tick this box to disable the "Has Children" checkbox filter in search form'),
      '#description' => $this->t("Disable the filter on parents with children checkbox in search form"),
      '#default_value' => $config->get('disable_children_filter'),
    ];

    // Add cloud coverage filter.
    $form['enable_cloud_coverage'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable cloud coverage search filter in search form',
      '#default_value' => $config->get('enable_cloud_coverage'),
    ];
    $form['cloud_coverage_details'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the cloud coverage filter inside a closed details tag in search form'),
      '#size' => 15,
      '#default_value' => $config->get('cloud_coverage_details'),
      '#states' => [
        'visible' => [
          ':input[name="enable_cloud_coverage"]' => ['checked' => TRUE],
        ],
      ],
    ];
    /*
    $form['ts_button_text'] = [
    '#type' => 'textfield',
    '#title' => t('Enter button text for TimeSeries visualization'),
    //  '#description' => t("the button text for HTTP access "),
    '#size' => 20,
    '#default_value' => $config->get('ts_button_text'),
    ];
    $form['csv_button_text'] = [
    '#type' => 'textfield',
    '#title' => t('Enter button text for CSV download'),
    //  '#description' => t("the button text for HTTP access "),
    '#size' => 20,
    '#default_value' => $config->get('csv_button_text'),
    ];

     */
    // Choose view_mode for display landing page draft.
    $form['searchmap'] = [
      '#type' => 'fieldset',
      '#title' => 'Configure Search map / result map',
      '#tree' => TRUE,
    ];
    /*
    $form['searchmap']['map_base_layer_wms_north'] = [
    '#type' => 'url',
    '#title' => t('The url for the northern basemap'),
    //'#description' => t("url northern base map"),
    '#size' => 60,
    '#default_value' => $config->get('map_base_layer_wms_north'),
    ];
    $form['searchmap']['map_base_layer_wms_south'] = [
    '#type' => 'url',
    '#title' => t('The url for the southern basemap'),
    //'#description' => t("url southern base map"),
    '#size' => 60,
    '#default_value' => $config->get('map_base_layer_wms_south'),
    ];
     */

    $form['searchmap']['init_proj'] = [
      '#type' => 'select',
      '#title' => $this->t('Select map projection'),
    // '#description' => t("Select map projection"),
      '#options' => [
        'EPSG:4326' => $this->t('EPSG:4326'),
        'EPSG:32661' => $this->t('UPS North'),
        'EPSG:32761' => $this->t('UPS South'),
      ],
      '#default_value' => $config->get('map_init_proj'),
    ];

    $form['searchmap']['additional_layers'] = [
      '#type' => 'select',
      '#title' => $this->t('Use additional layers'),
    // '#description' => t("Select whethever to use additional layers"),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' => $config->get('map_additional_layers'),
    ];

    $form['searchmap']['pins'] = [
      '#type' => 'select',
      '#title' => $this->t('Show pins on map'),
    // '#description' => t("Show pins on map or not."),
      '#options' => [
        1 => $this->t('Yes'),
        0 => $this->t('No'),
      ],
      '#default_value' => $config->get('map_pins'),
    ];

    $form['searchmap']['zoom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter the initial zoom value of map'),
    // '#description' => t("the initial zoom of the map "),
      '#size' => 20,
      '#default_value' => $config->get('map_zoom'),
    ];
    $form['searchmap']['location'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial map location'),
    // '#description' => t("Select initial map location "),
      '#options' =>
      array_combine(array_keys($config->get('map_locations')), array_keys($config->get('map_locations'))),

      '#default_value' => 'longyearbyen',
    ];
    $form['searchmap']['bbox_filter'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the default predicate for bounding box filter'),
      '#description' => $this->t("The default boundingbox filter predicate"),
      '#options' => [
        'intersects' => $this->t('Intersects'),
        'within' => $this->t('Within'),
      // 'contains' => 'Contains',
      // 'disjoint' => 'Disjoint',
      // 'equals' => 'Equals'
      ],
      '#default_value' => $config->get('map_bbox_filter'),
    ];

    $form['searchmap']['search_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Help text for search map'),
    // '#description' => t("this  help text will be displayed under the search map  "),
      '#default_value' => $config->get('map_search_text'),
    ];

    $form['searchmap']['wms_layers_skip'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Enter commaseperated list of WMS layers to be exluded from WMS Visualization'),
      '#description' => $this->t("The layer names must be the low case names with underscores from Capabilities XML <Layer><Name>"),
      '#default_value' => $config->get('map_wms_layers_skip'),
    ];
    /*
    $form['dar'] = [
    '#type' => 'fieldset',
    '#title' => 'Configure Data Access',
    '#tree' => TRUE,
    ];
    $form['dar']['http'] = [
    '#type' => 'textfield',
    '#title' => t('Enter button text for HTTP access'),
    //  '#description' => t("the button text for HTTP access "),
    '#size' => 20,
    '#default_value' => $config->get('dar_http'),
    ];
    $form['dar']['odata'] = [
    '#type' => 'textfield',
    '#title' => t('Enter button text for ODATA access'),
    //  '#description' => t("the button text for ODATA access "),
    '#size' => 20,
    '#default_value' => $config->get('dar_odata'),
    ];
    $form['dar']['opendap'] = [
    '#type' => 'textfield',
    '#title' => t('Enter button text for OPenDAP access'),
    //  '#description' => t("the button text for HTTP access "),
    '#size' => 20,
    '#default_value' => $config->get('dar_opendap'),
    ];
    $form['dar']['ogc_wms'] = [
    '#type' => 'textfield',
    '#title' => t('Enter button text for OGC WMS access'),
    //  '#description' => t("the button text for HTTP access "),
    '#size' => 20,
    '#default_value' => $config->get('dar_ogc_wms'),
    ];
     */
    // $form['#attached']['library'][] = 'landing_page_creator/landing_page_creator';
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * NOTE: Implement form validation here.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get user and pass from admin configuration.
    $values = $form_state->getValues();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /*
     * Save the configuration
     */
    $values = $form_state->getValues();

    if ($values['searchmap']['additional_layers'] === '1') {
      $layers = TRUE;
    }
    else {
      $layers = FALSE;
    }

    if ($values['searchmap']['pins'] === '1') {
      $pins = TRUE;
    }
    else {
      $pins = FALSE;
    }

    $this->configFactory->getEditable('metsis_search.settings')
          // ->set('map_base_layer_wms_north', $values['searchmap']['map_base_layer_wms_north'])
          // ->set('map_base_layer_wms_south', $values['searchmap']['map_base_layer_wms_south'])
      ->set('map_init_proj', $values['searchmap']['init_proj'])
      ->set('map_additional_layers_b', $layers)
      ->set('map_pins_b', $pins)
      ->set('map_additional_layers', $values['searchmap']['additional_layers'])
      ->set('map_pins', $values['searchmap']['pins'])
      ->set('map_zoom', $values['searchmap']['zoom'])
      ->set('map_selected_location', $values['searchmap']['location'])
      ->set('map_bbox_filter', $values['searchmap']['bbox_filter'])
      ->set('map_search_text', $values['searchmap']['search_text'])
      ->set('map_wms_layers_skip', $values['searchmap']['wms_layers_skip'])
          // ->set('dar_http', $values['dar']['http'])
          // ->set('dar_odata', $values['dar']['odata'])
          // ->set('dar_opendap', $values['dar']['opendap'])
          // ->set('dar_ogc_wms', $values['dar']['ogc_wms'])
          // ->set('lp_button_var', $values['lp_button_var'])
          // ->set('ts_server_type', $values['ts_server_type'])
          // ->set('ts_button_text', $values['ts_button_text'])
          // ->set('csv_button_text', $values['csv_button_text'])
      ->set('selected_collections', $values['collections'])
      ->set('pywps_service', $values['ts_pywps_url'])
      ->set('score_parent', $values['score_parent'])
      ->set('hide_add_to_basket', $values['hide_add_to_basket'])
      ->set('enable_cloud_coverage', $values['enable_cloud_coverage'])
      ->set('cloud_coverage_details', $values['cloud_coverage_details'])
      ->set('disable_children_filter', $values['disable_children_filter'])
          // ->set('keep_parent_filter', $values['keep_parent_filter'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
