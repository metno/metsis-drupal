<?php

/**
 * Build and return the form{
 */
function metsis_qsearch_zero_form($form, &$form_state) {
    global $metsis_conf;
//    sdpm($GLOBALS['_SERVER']['SERVER_NAME']);
//    sdpm($GLOBALS['conf']['site_name']);
//    sdpm($GLOBALS);
    /**
     * test{
     */
//    sdpm(adc_get_dataset_fields(SOLR_SERVER_IP,SOLR_SERVER_PORT,SOLR_CORE_CHILD, array('"bd4134ea-cc43-5584-96b4-7f43c7566047-c3"','"f87ddd87-cef9-5a94-bb64-c28cfdf80bbe"','"5f9416de-b309-5593-afd2-6ad47221801f"'),array('mmd_title','mmd_data_access_resource','mmd_collection')));
//    sdpm(adc_get_dataset_fields(SOLR_SERVER_IP,SOLR_SERVER_PORT,SOLR_CORE_CHILD, array('"bd4134ea-cc43-5584-96b4-7f43c7566047-c3"','"f87ddd87-cef9-5a94-bb64-c28cfdf80bbe"','"5f9416de-b309-5593-afd2-6ad47221801f"'),array('mmd_title','mmd_data_access_resource','mmd_collection'))['response']['docs'][0]['mmd_collection']);
//    sdpm(adc_get_dataset_fields(SOLR_SERVER_IP,SOLR_SERVER_PORT,SOLR_CORE_CHILD, array('"bd4134ea-cc43-5584-96b4-7f43c7566047-c3"','"f87ddd87-cef9-5a94-bb64-c28cfdf80bbe"','"5f9416de-b309-5593-afd2-6ad47221801f"'),array('mmd_metadata_identifier', 'mmd_title','mmd_data_access_resource','mmd_collection')));
    //$metadata_identifier_array = array('bd4134ea-cc43-5584-96b4-7f43c7566047-c3', 'f87ddd87-cef9-5a94-bb64-c28cfdf80bbe', '5f9416de-b309-5593-afd2-6ad47221801f', 'bd4134ea-cc43-5584-96b4-7f43c7566047-c3', 'e08a62a5-a87e-50fd-952b-80df2d32f9a5');
    //sdpm(adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_CHILD, $metadata_identifier_array, array('mmd_metadata_identifier', 'mmd_title', 'mmd_data_access_resource', 'mmd_collection'),0,100));
//    adc_get_dataset_fields(SOLR_SERVER_IP,SOLR_SERVER_PORT,SOLR_CORE_CHILD, array('"5f9416de-b309-5593-afd2-6ad47221801f"'),array('mmd_title','mmd_data_access_resource','mmd_collection'));
    /**
     * test}
     */
    //sdpm( drupal_realpath('public://'));
    $mqsearch_params = drupal_get_query_parameters();
    //
    $full_text_search = '';
    $finished_after = '';
    $finished_before = '';
    $bbox_top_left_lat = '';
    $bbox_top_left_lon = '';
    $bbox_bottom_right_lon = '';
    $bbox_bottom_right_lat = '';
    $institutions = [];
    $collections = [];
//    $platform_long_name = [];
    $cloud_cover_value = '';
    $investigator = '';
    $topics_and_variables = '';
    /**
     * nbs{
     */
//    $instrument_polarisation = [];
//    $product_type = [];
//    $instrument_mode = [];
    $c_platform = [];
    $c_s1a_cim = [];
    $c_s1a_cip = [];
    $c_s1b_cim = [];
    $c_s1b_cip = [];
    $c_s2a_cpt = [];
    $c_s2a_ccc = '';
    $c_s2b_cpt = [];
    $c_s2b_ccc = '';
    /**
     * nbs}
     */
    //

    if (isset($mqsearch_params['quid'])) {

        $mq_cached_form_state_values = mq_db_select($mqsearch_params['quid']);
        //
        $full_text_search = $mq_cached_form_state_values->chosen_full_text_search;
        //
        $finished_after = $mq_cached_form_state_values->finished_after;
        $finished_before = $mq_cached_form_state_values->finished_before;
        //
        $bbox_top_left_lat = $mq_cached_form_state_values->bbox_top_left_lat;
        $bbox_top_left_lon = $mq_cached_form_state_values->bbox_top_left_lon;
        $bbox_bottom_right_lon = $mq_cached_form_state_values->bbox_bottom_right_lon;
        $bbox_bottom_right_lat = $mq_cached_form_state_values->bbox_bottom_right_lat;
        //
        foreach ($mq_cached_form_state_values->institutions->chosen_institutions as $i) {
            if ($i !== 0) {
                array_push($institutions, $i);
            }
        };
        //
        foreach ($mq_cached_form_state_values->collections->chosen_collections as $i) {
            if ($i !== 0) {
                array_push($collections, $i);
            }
        };
        //
        //repopulate platform
        //NBS specific
        if (isset($mq_cached_form_state_values->platform_long_name)) {
            $platform_long_names = get_object_vars($mq_cached_form_state_values->platform_long_name);
            foreach ($platform_long_names as $plna) {
                if (isset($plna->chosen_platform_long_name->sentinel_1a)) {
                    if ($plna->chosen_platform_long_name->sentinel_1a === "sentinel_1a") {
                        array_push($c_platform,"sentinel_1a");
                        $c_s1a_cim = array_filter(get_object_vars($plna->instrument_modes->chosen_instrument_modes));
                        $c_s1a_cip = array_filter(get_object_vars($plna->instrument_polarisations->chosen_instrument_polarisations));
                    }
                }
                if (isset($plna->chosen_platform_long_name->sentinel_1b)) {
                    if ($plna->chosen_platform_long_name->sentinel_1b === "sentinel_1b") {
                        array_push($c_platform,"sentinel_1b");
                        $c_s1b_cim = array_filter(get_object_vars($plna->instrument_modes->chosen_instrument_modes));
                        $c_s1b_cip = array_filter(get_object_vars($plna->instrument_polarisations->chosen_instrument_polarisations));
                    }
                }
                if (isset($plna->chosen_platform_long_name->sentinel_2a)) {
                    if ($plna->chosen_platform_long_name->sentinel_2a === "sentinel_2a") {
                        array_push($c_platform,"sentinel_2a");
                        $c_s2a_cpt = array_filter(get_object_vars($plna->product_types->chosen_product_types));
                        $c_s2a_ccc = $plna->cloud_cover_value->chosen_cloud_cover_value;
                    }
                }
                if (isset($plna->chosen_platform_long_name->sentinel_2b)) {
                    if ($plna->chosen_platform_long_name->sentinel_2b === "sentinel_2b") {
                        array_push($c_platform,"sentinel_2b");
                        $c_s2b_cpt = array_filter(get_object_vars($plna->product_types->chosen_product_types));
                        $c_s2b_ccc = $plna->cloud_cover_value->chosen_cloud_cover_value;
                    }
                }
            }

//            sdpm(get_object_vars($mq_cached_form_state_values->platform_long_name));
            //$mq_cached_form_state_values->platform_long_name->sentinel_1a->instrument_modes->chosen_instrument_modes
            //$mq_cached_form_state_values->platform_long_name->sentinel_1a->instrument_polarisations
//            foreach ($mq_cached_form_state_values->platform_long_name as $pln) {
//                
//                if (isset($pln->chosen_platform_long_name->sentinel_1a)) {
//                    if ($pln->chosen_platform_long_name->sentinel_1a == 'sentinel_1a') {
//                        sdpm("sentinel_1a is chosen. check for sub elements");
//                        sdpm($mq_cached_form_state_values->platform_long_name->sentinel_1a->instrument_modes->chosen_instrument_modes);
//                        foreach ($pln->chosen_platform_long_name->sentinel_1a->instrument_modes->chosen_instrument_modes->values as $cim) {
//                            array_push($c_s1a_cim, $cim);
//                        }
//                    }
//                }
//            }
        }
//        
//        
//        foreach ($mq_cached_form_state_values->platform_long_name->chosen_platform_long_name as $pln) {
//            //sdpm($mq_cached_form_state_values->platform_long_name->sentinel_1a->chosen_platform_long_name)
//            sdpm($mq_cached_form_state_values->platform_long_name);
//            if ($pln !== 0) {
//                array_push($platform_long_name, $pln);
//            }
//        };
        //
//        $cloud_cover_value = $mq_cached_form_state_values->cloud_cover_value->chosen_cloud_cover_value;
        //
        $investigator = $mq_cached_form_state_values->chosen_investigator;
        //
        $topics_and_variables = $mq_cached_form_state_values->chosen_topics_and_variables_a;
        //
    }

    /**
     * "full text search field"{
     */
    $form['full_text_search'] = array(
      //TODO
      '#type' => 'fieldset',
      '#title' => t('Full text search'),
      '#collapsible' => TRUE,
      '#collapsed' => FULL_TEXT_SEARCH_INITIALLY_COLLAPSED,
      '#attributes' => array('class' => array('full-text-search-fieldset')),
    );

    $form['full_text_search']['chosen_full_text_search'] = array(
      '#type' => 'textfield',
      '#element_validate' => array('msb_text_qsearch_validate'),
      '#attributes' => array(
        'placeholder' => t('Search words'),
      ),
      '#default_value' => $full_text_search,
        // '#description' => t('Full text search'),
    );
    /*
     * "full text search field"}
     */
    /**
     * investigator{
     */
    // sdpm(RESULTS_PER_PAGE);
    if (INVESTIGATOR_VISIBLE) {
        $form['investigator'] = array(
          '#type' => 'fieldset',
          '#title' => t('Investigator'),
          '#collapsible' => TRUE,
          '#collapsed' => INVESTIGATOR_INITIALLY_COLLAPSED,
          '#attributes' => array('class' => array('investigator-fieldset')),
        );
        $form['investigator']['chosen_investigator'] = array(
          '#type' => 'textfield',
          // '#size' => '50',
          '#element_validate' => array('adc_investigator_validate'),
          '#attributes' => array(
            'placeholder' => t('Investigator\'s name'),
          ),
          //'#description' => t('Any combination of: A-Z, a-z, asterisk "*", spaces is allowed.'),
          '#default_value' => $investigator,
        );
    }
    /*
     * investigator}
     */

    /**
     * Topics and variables{
     */
    if (TOPICS_AND_VARIABLES_VISIBLE) {
        $form['topics_and_variables'] = array(
          '#type' => 'fieldset',
          '#title' => t('Topics and variables'),
          '#collapsible' => TRUE,
          '#collapsed' => TOPICS_AND_VARIABLES_INITIALLY_COLLAPSED,
          '#attributes' => array('class' => array('topics-and-variables-fieldset')),
        );
        $form['topics_and_variables']['chosen_topics_and_variables_a'] = array(
          '#type' => 'textfield',
          '#autocomplete_path' => METADATA_PREFIX . 'keywords/autocomplete',
          // '#required' => TRUE,
          // '#default_value' => $default_value,
          //'#default_value' => get_junk_args(),
          '#element_validate' => array('msb_text_qsearch_validate'),
          '#attributes' => array(
            'placeholder' => t('Science keyword'),
          ),
          //'#description' => t('Type in a science keyword like, "cryosphere" or "remote sensing"'),
          '#default_value' => $topics_and_variables,
        );
    }
    /**
     * Topics and varibles}
     */
    /**
     * operational status{
     */
    $form['operational_status'] = array(
      '#type' => 'hidden',
      // disabled for now at the request of project owner
      //'#type' => 'fieldset',
      '#title' => t('Operational status'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
      '#attributes' => array(
        'class' => array(
          'operational-status-fieldset',
        ),
        'title' => hack_get_skos_opstat("Operational Status"),
      ),
    );
    $form['operational_status']['chosen_operational_status'] = array(
      '#type' => 'checkboxes',
      '#options' => drupal_map_assoc(msb_get_operational_statuses()),
    );
    /**
     * operational status}
     */
    /**
     * Institutions{
     */
    if (INSTITUTIONS_VISIBLE) {
        $form['institutions'] = array(
          '#type' => 'fieldset',
          '#title' => t('Institutions'),
          '#collapsible' => TRUE,
          '#collapsed' => INSTITUTIONS_INITIALLY_COLLAPSED,
          '#tree' => TRUE,
          '#attributes' => array('class' => array('institutions-fieldset')),
        );
        $form['institutions']['chosen_institutions'] = array(
          '#type' => 'checkboxes',
          //'#title' => t('Institutions'),
          '#options' => drupal_map_assoc(msb_get_institutions()),
          '#default_value' => $institutions,
            //'#default_value' => explode(";", $institutions),
            //'#default_value' => array("Norwegian Meteorological Institute", "European Centre for Medium-Range Weather Forecasts"),
        );
    }
    /**
     * Institutions}
     */
    /**
     * Collection{
     */
    if (COLLECTIONS_VISIBLE) {
        $form['collections'] = array(
          '#type' => 'fieldset',
          '#title' => t('Collections'),
          '#collapsible' => TRUE,
          '#collapsed' => COLLECTIONS_INITIALLY_COLLAPSED,
          '#tree' => TRUE,
          '#attributes' => array('class' => array('collections-fieldset')),
        );
        $form['collections']['chosen_collections'] = array(
          '#type' => 'checkboxes',
          //'#title' => t('Collections'),
          '#options' => drupal_map_assoc(msb_facet_get_collections()),
          '#default_value' => $collections,
            //'#default_value' => explode(";", $collections),
        );
    }
    /**
     * Collection}
     */
    /**
     * platform_long_name{
     */
    $platform_long_name_array = q_get_platform_long_name();
    ksort($platform_long_name_array);
    /**
     * new NBS variables{
     * TODO 0
     * not implemented yet
     * these definitions will be removed when faceting is implemented
     * for these field in Solr and Drupal
     */
    //$product_type_array=q_get_product_type();
    if (defined('PRODUCT_TYPES')) {
        $product_types_array = explode(',', PRODUCT_TYPES);
        sort($product_types_array);
    }
    if (defined('INSTRUMENT_MODES')) {
        $instrument_modes_array = explode(',', INSTRUMENT_MODES);
        sort($instrument_modes_array);
    }
    if (defined('INSTRUMENT_POLARISATIONS')) {
        $instrument_polarisations_array = explode(',', INSTRUMENT_POLARISATIONS);
        sort($instrument_polarisations_array);
    }
//    $product_types_array = ['L1C', 'L2A'];
//    //$mode_array=q_get_mode();
//    $acquisition_modes_array = ['IW', 'EW'];
//    //$polarisation_modes_array=q_get_instrument_polarisations();
//    $instrument_polarisations_array = ['HH', 'VV', 'HH+HV', 'VV+VH'];
//    
//    define('INSTRUMENT_POLARISATIONS',$metsis_conf['instrument_polarisations']);
//}
//if(isset($metsis_conf['product_types'])){
//define('PRODUCT_TYPES',$metsis_conf['product_types']);
//}
//if(isset($metsis_conf['acquisition_modes'])){
//define('ACQUISITION_MODES',$metsis_conf['acquisition_modes']);
//}

    /**
     * new NBS variables}
     */
    if (PLATFORM_LONG_NAME_VISIBLE) {
//        
//        $form['platform_long_name'] = array(
//          '#type' => 'fieldset',
//          '#title' => t('Platform'),
//          '#collapsible' => TRUE,
//          '#collapsed' => PLATFORM_LONG_NAME_INITIALLY_COLLAPSED,
//          '#tree' => TRUE,
//          '#attributes' => array('class' => array('platform_long_name-fieldset')),
//        );
//        $form['platform_long_name']['chosen_platform_long_name'] = array(
//          '#type' => 'checkboxes',
//          //'#title' => t('Platform'),
//          //'#options' => drupal_map_assoc(q_get_platform_long_name()),
//          '#options' => array_combine(array_values($platform_long_name_array), array_values($platform_long_name_array)),
//          '#default_value' => $platform_long_name,
//          //'#default_value' => explode(";", $platform_long_name),
//          // '#default_value' => mq_db_select($mqsearch_params['query_id'])->platform_long_name->chosen_platform_long_name,
//          '#attributes' => array(
//            'class' => array(
//              'platform-long-name',
//            ),
//          ),
//        );

        /**
         * test{
         * need to loop through the platforms and create the sub-fieldsets etc
         * according to platform name 
         */
        $form['platform_long_name'] = array(
          '#type' => 'fieldset',
          '#title' => t('Platform'),
          '#collapsible' => TRUE,
          '#collapsed' => PLATFORM_LONG_NAME_INITIALLY_COLLAPSED,
          '#tree' => TRUE,
          '#attributes' => array('class' => array('platform_long_name-fieldset')),
        );
        foreach ($platform_long_name_array as $k => $v) {
            $form['platform_long_name'][$k]['chosen_platform_long_name'] = array(
              '#type' => 'checkboxes',
              '#options' => array_combine(array($k), array($v)),
              '#default_value' => array_values($c_platform),
              '#attributes' => array(
                'class' => array(
                  'platform-long-name-check-box',
                ),
              ),
            );
            if ($k == 'sentinel_1a') {
                if (INSTRUMENT_MODES_VISIBLE) {
                    $form['platform_long_name'][$k]['instrument_modes'] = array(
                      //  '#prefix' => '<div id="xxxx">',
                      '#type' => 'fieldset',
                      '#title' => t('Mode'),
                      '#collapsible' => TRUE,
                      '#collapsed' => TRUE,
                      '#tree' => TRUE,
                        //  '#suffix' => '</div>',
                    );
                    $form['platform_long_name'][$k]['instrument_modes']['chosen_instrument_modes'] = array(
                      '#type' => 'checkboxes',
                      '#options' => array_combine(array_values($instrument_modes_array), array_values($instrument_modes_array)),
                      '#default_value' => $c_s1a_cim,
                        //'#options' => drupal_map_assoc($instrument_modes_array),
                    );
                    if (INSTRUMENT_POLARISATION_VISIBLE) {
                        $form['platform_long_name'][$k]['instrument_polarisations'] = array(
                          '#type' => 'fieldset',
                          '#title' => t('Polarisation'),
                          '#collapsible' => TRUE,
                          '#collapsed' => TRUE,
                          '#tree' => TRUE,
                        );
                        $form['platform_long_name'][$k]['instrument_polarisations']['chosen_instrument_polarisations'] = array(
                          '#type' => 'checkboxes',
                          '#options' => array_combine(array_values($instrument_polarisations_array), array_values($instrument_polarisations_array)),
                          '#default_value' => $c_s1a_cip,
                            //'#options' => drupal_map_assoc($instrument_polarisations_array), 
                        );
                    }
                }
            }
            if ($k == 'sentinel_1b') {
                if (INSTRUMENT_MODES_VISIBLE) {
                    $form['platform_long_name'][$k]['instrument_modes'] = array(
                      //  '#prefix' => '<div id="xxxx">',
                      '#type' => 'fieldset',
                      '#title' => t('Mode'),
                      '#collapsible' => TRUE,
                      '#collapsed' => TRUE,
                      '#tree' => TRUE,
                        //  '#suffix' => '</div>',
                    );
                    $form['platform_long_name'][$k]['instrument_modes']['chosen_instrument_modes'] = array(
                      '#type' => 'checkboxes',
                      '#options' => array_combine(array_values($instrument_modes_array), array_values($instrument_modes_array)),
                        //'#options' => drupal_map_assoc($instrument_modes_array),
                    );
                    if (INSTRUMENT_POLARISATION_VISIBLE) {
                        $form['platform_long_name'][$k]['instrument_polarisations'] = array(
                          '#type' => 'fieldset',
                          '#title' => t('Polarisation'),
                          '#collapsible' => TRUE,
                          '#collapsed' => TRUE,
                          '#tree' => TRUE,
                        );
                        $form['platform_long_name'][$k]['instrument_polarisations']['chosen_instrument_polarisations'] = array(
                          '#type' => 'checkboxes',
                          '#options' => array_combine(array_values($instrument_polarisations_array), array_values($instrument_polarisations_array)),
                            //'#options' => drupal_map_assoc($instrument_polarisations_array), 
                        );
                    }
                }
            }
            if ($k == 'sentinel_2a') {
                if (PRODUCT_TYPES_VISIBLE) {
                    $form['platform_long_name'][$k]['product_types'] = array(
                      '#type' => 'fieldset',
                      '#title' => t('Product type'),
                      '#collapsible' => TRUE,
                      '#collapsed' => TRUE,
                      '#tree' => TRUE,
                    );
                    $form['platform_long_name'][$k]['product_types']['chosen_product_types'] = array(
                      '#type' => 'checkboxes',
                      '#options' => array_combine(array_values($product_types_array), array_values($product_types_array)),
                        //'#options' => drupal_map_assoc($product_types_array), 
                    );
                }
                if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
                    $form['platform_long_name'][$k]['cloud_cover_value'] = array(
                      '#type' => 'fieldset',
                      '#title' => t('Cloud cover [%] of scene'),
                      '#collapsible' => TRUE,
                      '#collapsed' => TRUE,
                      '#tree' => TRUE,
                      '#attributes' => array('class' => array('cloud_cover_value-fieldset')),
                    );
                    $form['platform_long_name'][$k]['cloud_cover_value']['chosen_cloud_cover_value'] = array(
                      '#type' => 'radios',
                      '#options' => drupal_map_assoc($metsis_conf['cloud_cover_value_search_options']),
                      '#default_value' => $c_s2a_ccc,
                    );
                }
            }

            if ($k == 'sentinel_2b') {
                if (PRODUCT_TYPES_VISIBLE) {
                    $form['platform_long_name'][$k]['product_types'] = array(
                      '#type' => 'fieldset',
                      '#title' => t('Product type'),
                      '#collapsible' => TRUE,
                      '#collapsed' => TRUE,
                      '#tree' => TRUE,
                    );
                    $form['platform_long_name'][$k]['product_types']['chosen_product_types'] = array(
                      '#type' => 'checkboxes',
                      '#options' => array_combine(array_values($product_types_array), array_values($product_types_array)),
                        //'#options' => drupal_map_assoc($product_types_array), 
                    );
                }
                if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
                    $form['platform_long_name'][$k]['cloud_cover_value'] = array(
                      '#type' => 'fieldset',
                      '#title' => t('Cloud cover [%] of scene'),
                      '#collapsible' => TRUE,
                      '#collapsed' => TRUE,
                      '#tree' => TRUE,
                      '#attributes' => array('class' => array('cloud_cover_value-fieldset')),
                    );
                    $form['platform_long_name'][$k]['cloud_cover_value']['chosen_cloud_cover_value'] = array(
                      '#type' => 'radios',
                      '#options' => drupal_map_assoc($metsis_conf['cloud_cover_value_search_options']),
                      '#default_value' => $c_s2b_ccc,
                    );
                }
            }
        }
//        $form['zzplatform_long_name']['zzchosen_platform_long_name_0'] = array(
//          '#type' => 'checkboxes',
//          '#options' => array($platform_long_name_array[0]),
//        );
//        $form['zzplatform_long_name']['zzchosen_platform_long_name_1'] = array(
//          '#type' => 'checkboxes',
//          '#options' => array($platform_long_name_array[1]),
//          '#default_value' => $platform_long_name,
//          '#attributes' => array(
//            'class' => array(
//              'zzplatform-long-name',
//            ),
//          ),
//        );
//        $form['zzplatform_long_name']['product_type'] = array(
//          '#type' => 'fieldset',
//          '#title' => t('zzPlatform product type'),
//          '#collapsible' => TRUE,
//          '#collapsed' => TRUE,
//          '#tree' => TRUE,
////          '#attributes' => array('class' => array('initially-hidden')),
//        );
//        $form['zzplatform_long_name']['product_type']['zzchosen_product_type'] = array(
//          '#type' => 'checkboxes',
//          '#options' => $product_type_array,
////          '#prefix' => '<div class="xyzproduct_type">',
////          '#suffix' => '</div>'
//        );
//        if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
//            $form['zzplatform_long_name']['cloud_cover_value'] = array(
//              '#type' => 'fieldset',
//              '#title' => t('Cloud cover [%] of scene'),
//              '#collapsible' => TRUE,
//              '#collapsed' => TRUE,
//              '#tree' => TRUE,
//                //'#attributes' => array('class' => array('cloud_cover_value-fieldset')),
//            );
//            $form['zzplatform_long_name']['cloud_cover_value']['chosen_cloud_cover_value'] = array(
//              '#type' => 'radios',
//              '#options' => drupal_map_assoc($metsis_conf['cloud_cover_value_search_options']),
//              '#default_value' => $cloud_cover_value,
//            );
//        }
//
//        $form['zzplatform_long_name']['zzchosen_platform_long_name'] = array(
//          '#type' => 'nested_checkboxes',
//          '#options' => array(
//            'sentinel_2a' => t("Sentinel-2A"),
//            'sentinel_2B' => array(
//              '#title' => t('Sentinel-2B'),
//              '#attributes' => array(
//                'class' => array('sentinel-2b')
//              ),
//              '#options' => array(
//                'product_type' => array(
//                  '#title' => t('Product type'),
//                  '#options' => array(
//                    'product_type_1' => t('L1C'),
//                    'product_type_2' => t('L2A')
//                  ),
//                ),
//
//
//                'cloud_cover' => array(
//                  '#title' => t('Cloud cover'),
//                  '#options' => array(
//                    'cloud_cover_1' => t('<10%'),
//                    'cloud_cover_2' => t('<20%')
//                  ),
//                ),
//              ),
//            ),
//            'subset3' => t('Sub Set 3'),
//          )
//        );
//
//
//
//
//        $form['zzplatform_long_name']['zzchosen_platform_long_name'] = array(
//          '#type' => 'nested_checkboxes',
//          '#options' => array(
//            'subset1' => t("Sub Set 1"),
//            'subset2' => array(
//              '#title' => t('Sub Set 2'),
//              '#attributes' => array(
//                'class' => array('testclass1')
//              ),
//              '#options' => array(
//                'option1' => t('Option1'),
//                'option2' => array(
//                  '#title' => t('Inner level 1'),
//                  '#options' => array(
//                    'option1' => t('Option1'),
//                    'option2' => t('Option2')
//                  )
//                )
//              )
//            ),
//            'subset3' => t('Sub Set 3'),
//          )
//        );
//
//        foreach ($platform_long_name_array as $k => $v) {
//            $form['zzplatform_long_name'][$k] = array(
//              '#type' => 'checkboxes',
//              //'#title' => t('Platform'),
//              '#options' => array($v),
//              '#attributes' => array(
//                'class' => array(
//                  'zzplatform_long_name',
//                ),
//              ),
//            );
//            // if ($v == 'Sentinel-2B') {
//            $form['zzplatform_long_name'][$k]['product_type'] = array(
//              '#type' => 'fieldset',
//              '#title' => t('Product type'),
//              '#collapsible' => TRUE,
//              '#collapsed' => TRUE,
//              '#tree' => TRUE,
//            );
//            //}
//        }
//        
//        
//        
//        
//        $form['zzplatform_long_name']['zzchosen_platform_long_name'] = array(
//          '#type' => 'checkboxes',
//          //'#title' => t('Platform'),
//          //'#options' => drupal_map_assoc(q_get_platform_long_name()),
//          '#options' => array_combine(array_keys($platform_long_name_array), array_values($platform_long_name_array)),
//          '#default_value' => $platform_long_name,
//          //'#default_value' => explode(";", $platform_long_name),
//          // '#default_value' => mq_db_select($mqsearch_params['query_id'])->platform_long_name->chosen_platform_long_name,
//          '#attributes' => array(
//            'class' => array(
//              'zzplatform_long_name',
//            ),
//          ),
//        );
//
//
//        $product_type_array = ['L1C' => 'L1C', 'L2A' => 'L2A'];
//        $form['zzplatform_long_name']['zzchosen_platform_long_name']['product_type'] = array(
//          '#type' => 'checkboxes',
//          '#options' => array_combine(array_keys($product_type_array), array_values($product_type_array)),
//          '#default_value' => "zz default value",
//          '#prefix' => '<div class="product_type_details">',
//          '#suffix' => '</div>',
//          '#attributes' => array(
//            'class' => array(
//              'zzplatform_long_name_product_type',
//            ),
//          ),
//        );

        /**
         * test}
         */
    }
    /**
     * platform_long_name}
     */
    /**
     * platform_long_name{
     */
    global $metsis_conf;
    /**
     * test{
     */
    //$qsearch_qcache_cloud="<50%";
    /**
     * test}
     */
//    if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
//        $form['cloud_cover_value'] = array(
//          '#type' => 'fieldset',
//          '#title' => t('Cloud cover [%] of scene'),
//          '#collapsible' => TRUE,
//          '#collapsed' => CLOUD_COVER_VALUE_INITIALLY_COLLAPSED,
//          '#tree' => TRUE,
//          '#attributes' => array('class' => array('cloud_cover_value-fieldset')),
//        );
//        $form['cloud_cover_value']['chosen_cloud_cover_value'] = array(
//          //'#type' => 'checkboxes',
//          '#type' => 'radios',
//          //'#title' => t('Cloud cover [%] of scene'),
//          //'#options' => drupal_map_assoc(msb_get_cloud_cover_value()),
//          '#options' => drupal_map_assoc($metsis_conf['cloud_cover_value_search_options']),
//          '#default_value' => $cloud_cover_value,
//            //'#default_value' => isset($qsearch_qcache_cloud) ? $qsearch_qcache_cloud : "",
//        );
    /**
     * test{
     */
    //sdpm($form_state);
    /**
     * test}
     */
//    }
    /**
     * cloud_cover_value}
     */
    /**
     * Geographical search{
     */
    //TODO: should use standard Drupal methods instead.
    //$jquery_path = "https://" . DRUPAL_SERVER . "/" . "/sites/all/modules/jquery_update/replace/jquery/1.10/jquery.min.js";
    $jquery_path = "/sites/all/modules/jquery_update/replace/jquery/1.10/jquery.min.js";
    $form['geographical_search'] = array(
      '#type' => 'fieldset',
      '#title' => t('Geographical search'),
      '#collapsible' => TRUE,
      // setting collapsed to TRUE prevents the map from being loaded - this is probably a timing issue.
      '#collapsed' => FALSE,
      '#attached' => array(
        'js' => array(
          $jquery_path,
        ),
      ),
      '#attributes' => array(
        'class' => array(
          'geographical-search-fieldset',
        // 'under_construction',
        ),
      ),
    );
    /**
     * data collection period{
     */
    $form['data_collection_period'] = array(
      '#type' => 'fieldset',
      '#title' => t('Data collection period'),
      '#collapsible' => TRUE,
      '#collapsed' => DATA_COLLECTION_PERIOD_INITIALLY_COLLAPSED,
      '#attributes' => array(
        'class' => array(
          'data-collection-period-fieldset',
        ),
      // 'msb-tooltip' => "",
      ),
    );
    /**
     * test{
     */
//    if(defined('SEARCH_MAX_METADATA_AGE')){
//        $search_max_metadata_age = SEARCH_MAX_METADATA_AGE;
//    }
    /**
     * test}
     */
    //TODO5 need to make data age default start date optional. No good to have to define something in 
    //the config file!!
    if ($finished_after != '') {
        $default_finished_after = $finished_after;
    }
    elseif ($metsis_conf['default_start_date']) {
        $default_finished_after = $metsis_conf['default_start_date'];
    }
    else {
        $default_finished_after = msb_get_short_isodate(adc_get_now_minus_hours(SEARCH_MAX_METADATA_AGE));
    }
    if ($finished_before != '') {
        $default_finished_before = $finished_before;
    }
    elseif ($metsis_conf['default_end_date']) {
        $default_finished_before = $metsis_conf['default_end_date'];
    }
    else {
        $default_finished_before = "";
    }

    $form['data_collection_period'][] = array(
      '#type' => 'item',
      //'#title' => t('start date'),
      'finished_after' => array(
        '#type' => 'date_popup',
        '#title' => t('Start date'),
        '#default_value' => $default_finished_after,
        //'#default_value' => $metsis_conf['default_start_date'] ? $metsis_conf['default_start_date'] : msb_get_short_isodate(adc_get_now_minus_hours(SEARCH_MAX_METADATA_AGE)),
        //'#default_value' => msb_get_short_isodate(adc_get_now_minus_hours(SEARCH_MAX_METADATA_AGE)),
        '#date_format' => 'Y-m-d',
        '#date_year_range' => '-50:+2',
        '#datepicker_options' => array(
          'changeMonth' => TRUE,
          'changeYear' => TRUE,
        // 'minDate' => 0,
        // 'maxDate' => 0
        ),
        '#element_validate' => array('msb_start_finish_date_validate'),
        '#attributes' => array(
          'placeholder' => t('yyyy-mm-dd'),
          'class' => array(
            'adc-highlight',
          // 'datepicker',
          // 'beautytips',
          ),
          'title' => "Start date BEFORE or FROM",
        // 'msb-tooltip' => "Filling this field will result in finding data created AFTER given date.",
        ),
      ),
      'finished_before' => array(
        '#type' => 'date_popup',
        '#title' => t('End date'),
        '#default_value' => $default_finished_before,
        //'#default_value' => $metsis_conf['default_end_date'],
        //'#default_value' => $metsis_conf['default_end_date'] ? defined($metsis_conf['default_end_date'])  : date('Y-m-d'),
        //'#default_value' => date('Y-m-d'),
        '#date_format' => 'Y-m-d',
        '#date_year_range' => '-50:+2',
        '#datepicker_options' => array(
        // 'minDate' => 0,
        // 'maxDate' => 0
        ),
        '#element_validate' => array('msb_start_finish_date_validate'),
        '#attributes' => array(
          'placeholder' => t('yyyy-mm-dd'),
          'class' => array(
            'adc-highlight',
          //'datepicker',
          // 'beautytips',
          ),
          'title' => "End date FROM or AFTER",
        // 'msb-tooltip' => "Filling this field will result in finding data created BEFORE given date.",
        ),
      //'#description' => t('Some desciption.'),
      //'#after_build' => array('msb_form_uidatepicker'),
      ),
    );
    /*
     * map coordinate search{
     */

    $form['bounding_box'] = array(
      '#type' => 'fieldset',
      '#title' => t('Bounding box'),
      //  '#weight' => 11,
      '#collapsible' => TRUE,
      '#collapsed' => BOUNDING_BOX_INITIALLY_COLLAPSED,
      '#attributes' => array(
        'class' => array(
          'bounding-box-fieldset',
        ),
      ),
    );
    $form['bounding_box'][] = array(
      '#type' => 'item',
      '#title' => t('Top left longitude'),
      'bbox_top_left_lon' => array(
        '#type' => 'textfield',
        '#element_validate' => array('adc_longitude_validate'),
        '#attributes' => array(
          'placeholder' => t('Top left longitude'),
          'title' => "Top left longitude",
        ),
        '#default_value' => $bbox_top_left_lon,
      ),
      'bbox_top_left_lat' => array(
        '#type' => 'textfield',
        '#element_validate' => array('adc_latitude_validate'),
        '#title' => t('Top left latitude'),
        '#attributes' => array(
          'placeholder' => t('Top left latitude'),
          'title' => "Top left latitude",
        ),
        '#default_value' => $bbox_top_left_lat,
      ),
      'bbox_bottom_right_lon' => array(
        '#type' => 'textfield',
        '#element_validate' => array('adc_longitude_validate'),
        '#title' => t('Bottom right longitude'),
        '#attributes' => array(
          'placeholder' => t('Bottom right longitude'),
          'title' => "Bottom right longitude",
        ),
        '#default_value' => $bbox_bottom_right_lon,
      ),
      'bbox_bottom_right_lat' => array(
        '#type' => 'textfield',
        '#element_validate' => array('adc_latitude_validate'),
        '#title' => t('Bottom right latitude'),
        '#default_value' => $bbox_bottom_right_lat,
        '#attributes' => array(
          'placeholder' => t('Bottom right latitude'),
          'title' => "Bottom right latitude",
        ),
      //'#default_value' => $bbox_bottom_right_lat,
      ),
    );
    //
    /*
     * map coordinate search}
     */
    /**
     * geographical search map
     */
    $form['geographical_search']['map'] = array(
      '#prefix' => ' ',
      '#markup' => adc_get_geographical_search_map(),
      '#suffix' => ' ',
    );
    $form['#validate'][] = 'msb_all_or_none_latlon_validate';
    $form['#validate'][] = 'msb_foo_validate';
    /*
     * submit button{
     */
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
      '#submit' => array('metsis_qsearch_submit'),
    );
    /*
     * submit button}
     */
    /*
     * reset button{
     */
    if (FORM_RESET_VISIBLE) {

        $form['options']['reset'] = array(
          '#type' => 'submit',
          '#value' => t('Reset'),
          '#submit' => array('metsis_qsearch_form_reset'),
        );
    }

    /*
     * reset button}
     */
    /*
     * goto_basket button{
     */
    //TODO: need to check that the user has > 0 items in basket (function TODO)
    if (BASKET_ELEMENTS_VISIBLE) {
        global $user;
        if (($user->uid) && get_user_item_count($user->uid) > 0) {
            $form['goto_basket'] = array(
              // '#type' => 'button',
              '#type' => 'submit',
//              '#value' => t('Basket (' . get_user_item_count($user->uid) . ')'),
              '#value' => t('Basket (@basket_item_count)', array('@basket_item_count' => get_user_item_count($user->uid))),
              '#submit' => array('adc_goto_basket'),
              '#validate' => array(),
              '#attributes' => array(
                'class' => array(
                  'adc-button-small',
                //  'goto-basket-button',
                ),
              ),
            );
            //empty basket
            $form['empty_basket'] = array(
              // '#type' => 'button',
              '#type' => 'submit',
              '#value' => t('Empty basket'),
              '#submit' => array('adc_empty_basket'),
              '#validate' => array(),
              '#attributes' => array(
                'class' => array(
                  'adc-button-small',
                //  'empty-basket-button',
                ),
              ),
            );
        }
    }
    /*
     * goto basket button}
     */

    /*
     * theme the form{
     */
    $path = drupal_get_path('module', 'metsis_qsearch');

    $form['#attached'] = array
      (
      'css' => array
        (
        'type' => 'file',
        'data' => $path . '/css/metsis_qsearch.css',
      ),
      'js' => array
        (
        'type' => 'file',
        'data' => $path . '/js/metsis_qsearch.js',
      ),
    );
    /*
     * theme the form}
     */
    //return system_settings_form($form);
//    sdpm($form);
//    sdpm($form_state);
    return $form;
}

function metsis_qsearch_submit($form, &$form_state) {
//    sdpm($form_state['values']);
    //sdpm(form_state_values_clean($form_state));
    //sdpm('redirection');
    $initial_user_query = msb_get_final_solr_qq($form_state);
    $_SESSION['qsearch']['initial_user_query'] = $initial_user_query;
    $bytes = 32;
    $quid = bin2hex(openssl_random_pseudo_bytes($bytes)) . (string) time();
    $_SESSION['qsearch']['quid'] = $quid;

    /**
     * test{
     */
    //$bytes = 32;
    //sdpm(bin2hex(openssl_random_pseudo_bytes($bytes)) . (string) time());
    //metsis_qcache_insert();
    //TODO
    //if $_SESSION['qsearch']['qcache_key'] is passed in populate form from qcache
    //otherwise set qcache_key on submit and store search in qcache
    //must also implement hook_cron() to remove searches from qchache based on a 
    //configurable age
    //$_SESSION['qsearch']['qcache_key'] = bin2hex(openssl_random_pseudo_bytes(32)) . (string) time();
    //sdpm(json_encode($form_state['values']));
    //mq_db_insert(mq_get_fields($form_state['values'], bin2hex(openssl_random_pseudo_bytes($bytes)) . (string) time(), session_id()));

    mq_db_insert(mq_get_fields($form_state['values'], $_SESSION['qsearch']['quid'], session_id()));
    /**
     * test}
     */
    $form_state['redirect'] = array
      (
      'results/',
      array
        (
        'query' => array
          (
//          'number_of_rows' => RESULTS_PER_PAGE,
//          'start_row' => 0,
          'page' => 1,
        ),
      ),
    );
}

//function metsis_qsearch_zero_form_submit_handler($form, &$form_state) {
//    $form_state['redirect'] = 'qsearch_results';
//}
//where is this used??
function qsearch_results_page() {
    $params = drupal_get_query_parameters();
    return q_get_paged_form();
}

//
///*
// * implement hook_theme{
// */
//
//function metsis_qsearch_theme() {
//    return array
//      (
//      'metsis_qsearch_form' => array
//        (
//        'render element' => 'form'
//      ),
//    );
//}
//
///*
// * implement hook_theme}
// */
/*
 * implement theme function for form{
 */

function theme_metsis_qsearch_form($variables) {
    global $metsis_conf;
    $form = $variables['form'];
    $output = '';
    //$output = '<h2>' . t('METADATA search') . '</h2>';
    $output .= '<div class="non-map-div">';
    //  
    $output .= '<div class="full-text-search-div">';
    $output .= drupal_render($form['full_text_search']);
    $output .= '</div>';
    //
    if (COLLECTION_PERIOD_VISIBLE) {
        $output .= '<div class="data-collection-period-div">';
        $output .= drupal_render($form['data_collection_period']);
        $output .= '</div>';
    }
    //  
    $output .= '<div class="bounding-box-div">';
    $output .= drupal_render($form['bounding_box']);
    $output .= '</div>';
    //
    if (INSTITUTIONS_VISIBLE) {
        $output .= '<div class="institutions-div">';
        $output .= drupal_render($form['institutions']);
        $output .= '</div>';
    }
    if (COLLECTIONS_VISIBLE) {
        $output .= '<div class="collections-div">';
        $output .= drupal_render($form['collections']);
        $output .= '</div>';
    }
    if (PLATFORM_LONG_NAME_VISIBLE) {
        $output .= '<div class="platform_long_name-div">';
        $output .= drupal_render($form['platform_long_name']);
        $output .= '</div>';
    }
//    if (PLATFORM_LONG_NAME_VISIBLE) {
//        $output .= '<div class="platform_long_name-div">';
//        $output .= drupal_render($form['zzplatform_long_name']);
//        $output .= '</div>';
//    }
    /**
     * test{
     */
    $output .= '<div class="habeli initially-hidden">';
    $output .= drupal_render($form['yyplatform_long_name']['sentinel_2b']['chosen_product_types']);
    $output .= '</div>';
    /**
     * test}
     */
    //
//    if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
//        $output .= '<div class="cloud_cover_value-div">';
//        $output .= drupal_render($form['cloud_cover_value']);
//        $output .= '</div>';
//    }
    //
    if (INVESTIGATOR_VISIBLE) {
        $output .= '<div class="investigator-div">';
        $output .= drupal_render($form['investigator']);
        $output .= '</div>';
    }
    //
    if (TOPICS_AND_VARIABLES_VISIBLE) {
        $output .= '<div class="topics-and-variables-div">';
        $output .= drupal_render($form['topics_and_variables']);
        $output .= '</div>';
    }
    //
    $output .= '<div class="operational-status-div">';
    $output .= drupal_render($form['operational_status']);
    $output .= '</div>';
    ////
    ////form-action-div{
    $output .= '<div class="form-action-div">';
    ////form-submit-div{
    //$output .= '<div class="form-submit-div">';
    $output .= drupal_render($form['submit']);
    //$output .= '</div>';
    ////form-submit-div}
    ////form-reset-div{
    ////$output .= '<div class="adc-button adc-sbutton">'; 
    //$output .= '<div class="form-reset-div">';
    ////$output .= '<a class="adc-button adc-sbutton">';
    //$output .= '<a>';
    $output .= drupal_render($form['options']['reset']);
    if (FORM_RESET_VISIBLE) {
        $output .= drupal_render($form['reset']);
    }
    //$output .= '</a>';
    //$output .= '</div>';
    ////goto-basket-div{
    if (BASKET_ELEMENTS_VISIBLE) {
        global $user;
        if (($user->uid) && get_user_item_count($user->uid) > 0) {
            $output .= drupal_render($form['goto_basket']);
            $output .= drupal_render($form['empty_basket']);
        }
    }
    ////goto-basket-div}
    $output .= '</div>';
    ////form-reset-div}

    $output .= '</div>';
    ////form-action-div}
    ////map-div{
    $output .= '<div class="map-div">';
    ////
    $output .= '<div class="geographical-search-div">';
    $output .= drupal_render($form['geographical_search']);
    $output .= '</div>';
    ////
    $output .= '</div>';

    $output .= drupal_render_children($form);

    return $output;
}

/*
 * implement theme function for form}
 */


/**
 * misc functions to be refactored
 */

//function _metsis_qsearch_options_after_build(&$element, &$form_state){
//    // Each renderable radio element.
//    foreach (element_children($element) as $key) {
//        $element[$key]['#prefix'] = '<div class="class1 class2">';
//        $element[$key]['#suffix'] = '</div>';
//    }
//    // Always return the element to render in after_build callbacks.
//    return $element;
//}