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
      '#title' => 'Enable cloud coverage search filter in main search form',
      '#default_value' => $config->get('enable_cloud_coverage'),
    ];
    $form['cloud_coverage_details'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the cloud coverage filter inside a closed details tag in the main search form'),
      '#size' => 15,
      '#default_value' => $config->get('cloud_coverage_details'),
      '#states' => [
        'visible' => [
          ':input[name="enable_cloud_coverage"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Add cloud coverage filter.
    $form['enable_cloud_coverage_elements'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable cloud coverage search filter in elements search form',
      '#default_value' => $config->get('enable_cloud_coverage_elements'),
    ];
    $form['cloud_coverage_details_elements'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the cloud coverage filter inside a closed details tag in the elements search form'),
      '#size' => 15,
      '#default_value' => $config->get('cloud_coverage_details_elements'),
      '#states' => [
        'visible' => [
          ':input[name="enable_cloud_coverage_elements"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Netcdf on demand config.
    $form['enable_netcdf_ondemand'] = [
      '#type' => 'checkbox',
      '#title' => 'Check this to enable the NetCDF OnDemand button',
      '#default_value' => $config->get('enable_netcdf_ondemand'),
    ];
    $form['netcdf_ondemand_service_endpoint'] = [
      '#type' => 'url',
      '#title' => 'Enter the url endpoint for the NetCDF OnDemand service',
      '#default_value' => $config->get('netcdf_ondemand_service_endpoint'),
      '#states' => [
        'visible' => [
          ':input[name="enable_netcdf_ondemand"]' => ['checked' => TRUE],
        ],
      ],
    ];
    // Add cloud coverage filter.
    $form['bbox_overlap_sort'] = [
      '#type' => 'checkbox',
      '#title' => 'Sort results by overlap-ratio when using bounding box filter',
      '#default_value' => $config->get('bbox_overlap_sort'),
    ];

    // Always sort by score first.
    $form['search_sort_score'] = [
      '#type' => 'checkbox',
      '#title' => 'Sort results by score before other sorts',
      '#default_value' => $config->get('search_sort_score'),
    ];

    $form['search_match_children'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check this box to also search within children.'),
      '#description' =>
      $this->t("Will return the parent of matching children when only returning level 1 datasets."),
      '#default_value' => $config->get('search_match_children'),
    ];
    $form['remove_parent_zero_children'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check this box to remove parent from search results if children subquery returns 0'),
      '#description' =>
      $this->t("Will remove the parent from the search results, if the children subquery returns zero children."),
      '#default_value' => $config->get('remove_parent_zero_children'),
    ];

    $form['remove_keys_zero_children'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check this box to remove the search fulltext key if children subquery returns 0'),
      '#description' =>
      $this->t("Will remove the searched fulltext keys from the child subquery so that the child button will show more than 0 results."),
      '#default_value' => $config->get('remove_keys_zero_children'),
    ];

    $form['show_bbox_filter_exposed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check this box to show the bbox filter in the search exposed form.'),
      '#description' => $this->t("A bbox filter element will be added to the search exosed form. Visually hidden by default"),
      '#default_value' => $config->get('show_bbox_filter_exposed'),
    ];
    $form['hide_bbox_filter_exposed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Only show the bbox exposed form when a bbox filer is active.'),
      '#description' => $this->t("The bbox exposed form will only be shown when a bbox filter is active"),
      '#default_value' => $config->get('hide_bbox_filter_exposed'),
      '#states' => [
        'visible' => [
          ':input[name="show_bbox_filter_exposed"]' => ['checked' => TRUE],
        ],
      ],
    ];

    /*
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
    $form['searchmap']['autosubmit'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check this box to autosubmit the search when a boundingbox filter are drawn on the map.'),
      '#description' => $this->t("Autosubmit the search when a boundingbox filter are drawn on the map"),
      '#default_value' => $config->get('map_bbox_autosubmit'),
    ];

    $form['searchmap']['search_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Help text for search map'),
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
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

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
      ->set('map_init_proj', $values['searchmap']['init_proj'])
      ->set('map_additional_layers_b', $layers)
      ->set('map_pins_b', $pins)
      ->set('map_additional_layers', $values['searchmap']['additional_layers'])
      ->set('map_pins', $values['searchmap']['pins'])
      ->set('map_zoom', $values['searchmap']['zoom'])
      ->set('map_selected_location', $values['searchmap']['location'])
      ->set('map_bbox_filter', $values['searchmap']['bbox_filter'])
      ->set('map_bbox_autosubmit', $values['searchmap']['autosubmit'])
      ->set('map_search_text', $values['searchmap']['search_text'])
      ->set('map_wms_layers_skip', $values['searchmap']['wms_layers_skip'])
      ->set('selected_collections', $values['collections'])
      ->set('pywps_service', $values['ts_pywps_url'])
      ->set('score_parent', $values['score_parent'])
      ->set('hide_add_to_basket', $values['hide_add_to_basket'])
      ->set('enable_cloud_coverage', $values['enable_cloud_coverage'])
      ->set('cloud_coverage_details', $values['cloud_coverage_details'])
      ->set('disable_children_filter', $values['disable_children_filter'])
      ->set('cloud_coverage_details_elements', $values['cloud_coverage_details_elements'])
      ->set('enable_cloud_coverage_elements', $values['enable_cloud_coverage_elements'])
      ->set('bbox_overlap_sort', $values['bbox_overlap_sort'])
      ->set('search_match_children', $values['search_match_children'])
      ->set('show_bbox_filter_exposed', $values['show_bbox_filter_exposed'])
      ->set('hide_bbox_filter_exposed', $values['hide_bbox_filter_exposed'])
      ->set('remove_parent_zero_children', $values['remove_parent_zero_children'])
      ->set('remove_keys_zero_children', $values['remove_keys_zero_children'])
      ->set('search_sort_score', $values['search_sort_score'])
      ->set('enable_netcdf_ondemand', $values['enable_netcdf_ondemand'])
      ->set('netcdf_ondemand_service_endpoint', $values['netcdf_ondemand_service_endpoint'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
