<?php

function metsis_qsearch_zero_form($form, &$form_state) {
    global $metsis_conf;
    $form['full_text_search'] = array('#type' => 'fieldset', '#title' => t('Full text search'), '#collapsible' => TRUE, '#collapsed' => FALSE, '#attributes' => array('class' => array('full-text-search-fieldset')),);
    $form['full_text_search']['chosen_full_text_search'] = array('#type' => 'textfield', '#element_validate' => array('msb_text_qsearch_validate'), '#attributes' => array('placeholder' => t('Search words'),),);
    if (INVESTIGATOR_VISIBLE) {
        $form['investigator'] = array('#type' => 'fieldset', '#title' => t('Investigator'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#attributes' => array('class' => array('investigator-fieldset')),);
        $form['investigator']['chosen_investigator'] = array('#type' => 'textfield', '#element_validate' => array('adc_investigator_validate'), '#attributes' => array('placeholder' => t('Investigator\'s name'),),);
    } if (TOPICS_AND_VARIABLES_VISIBLE) {
        $form['topics_and_variables'] = array('#type' => 'fieldset', '#title' => t('Topics and variables'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#attributes' => array('class' => array('topics-and-variables-fieldset')),);
        $form['topics_and_variables']['chosen_topics_and_variables_a'] = array('#type' => 'textfield', '#autocomplete_path' => METADATA_PREFIX . 'keywords/autocomplete', '#element_validate' => array('msb_text_qsearch_validate'), '#attributes' => array('placeholder' => t('Science keyword'),),);
    } $form['operational_status'] = array('#type' => 'hidden', '#title' => t('Operational status'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE, '#attributes' => array('class' => array('operational-status-fieldset',), 'title' => hack_get_skos_opstat("Operational Status"),),);
    $form['operational_status']['chosen_operational_status'] = array('#type' => 'checkboxes', '#options' => drupal_map_assoc(msb_get_operational_statuses()),);
    if (INSTITUTIONS_VISIBLE) {
        $form['institutions'] = array('#type' => 'fieldset', '#title' => t('Institutions'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE, '#attributes' => array('class' => array('institutions-fieldset')),);
        $form['institutions']['chosen_institutions'] = array('#type' => 'checkboxes', '#options' => drupal_map_assoc(msb_get_institutions()),);
    } if (PLATFORM_LONG_NAME_VISIBLE) {
        $form['platform_long_name'] = array('#type' => 'fieldset', '#title' => t('Platform'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE, '#attributes' => array('class' => array('platform_long_name-fieldset')),);
        $form['platform_long_name']['chosen_platform_long_name'] = array('#type' => 'checkboxes', '#options' => drupal_map_assoc(q_get_platform_long_name()),);
    } global $metsis_conf;
    if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
        $form['cloud_cover_value'] = array('#type' => 'fieldset', '#title' => t('Cloud cover [%] of scene'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#tree' => TRUE, '#attributes' => array('class' => array('cloud_cover_value-fieldset')),);
        $form['cloud_cover_value']['chosen_cloud_cover_value'] = array('#type' => 'radios', '#options' => drupal_map_assoc($metsis_conf['cloud_cover_value_search_options']), '#default_value' => isset($qsearch_qcache_cloud) ? $qsearch_qcache_cloud : "",);
    } $jquery_path = "/sites/all/modules/jquery_update/replace/jquery/1.10/jquery.min.js";
    $form['geographical_search'] = array('#type' => 'fieldset', '#title' => t('Geographical search'), '#collapsible' => TRUE, '#collapsed' => FALSE, '#attached' => array('js' => array($jquery_path,),), '#attributes' => array('class' => array('geographical-search-fieldset',),),);
    $form['data_collection_period'] = array('#type' => 'fieldset', '#title' => t('Data collection period'), '#collapsible' => TRUE, '#collapsed' => FALSE, '#attributes' => array('class' => array('data-collection-period-fieldset',),),);
    $form['data_collection_period'][] = array('#type' => 'item', 'finished_after' => array('#type' => 'date_popup', '#title' => t('Start date'), '#default_value' => $metsis_conf['default_start_date'] ? $metsis_conf['default_start_date'] : msb_get_short_isodate(adc_get_now_minus_hours(SEARCH_MAX_METADATA_AGE)), '#date_format' => 'Y-m-d', '#date_year_range' => '-50:+2', '#datepicker_options' => array('changeMonth' => TRUE, 'changeYear' => TRUE,), '#element_validate' => array('msb_start_finish_date_validate'), '#attributes' => array('placeholder' => t('yyyy-mm-dd'), 'class' => array('adc-highlight',), 'title' => "Start date BEFORE or FROM",),), 'finished_before' => array('#type' => 'date_popup', '#title' => t('End date'), '#default_value' => $metsis_conf['default_end_date'], '#date_format' => 'Y-m-d', '#date_year_range' => '-50:+2', '#datepicker_options' => array(), '#element_validate' => array('msb_start_finish_date_validate'), '#attributes' => array('placeholder' => t('yyyy-mm-dd'), 'class' => array('adc-highlight',), 'title' => "End date FROM or AFTER",),),);
    $form['bounding_box'] = array('#type' => 'fieldset', '#title' => t('Bounding box'), '#collapsible' => TRUE, '#collapsed' => TRUE, '#attributes' => array('class' => array('bounding-box-fieldset',),),);
    $form['bounding_box'][] = array('#type' => 'item', '#title' => t('Top left longitude'), 'bbox_top_left_lon' => array('#type' => 'textfield', '#element_validate' => array('adc_longitude_validate'), '#attributes' => array('placeholder' => t('Top left longitude'), 'title' => "Top left longitude",),), 'bbox_top_left_lat' => array('#type' => 'textfield', '#element_validate' => array('adc_latitude_validate'), '#title' => t('Top left latitude'), '#attributes' => array('placeholder' => t('Top left latitude'), 'title' => "Top left latitude",),), 'bbox_bottom_right_lon' => array('#type' => 'textfield', '#element_validate' => array('adc_longitude_validate'), '#title' => t('Bottom right longitude'), '#attributes' => array('placeholder' => t('Bottom right longitude'), 'title' => "Bottom right longitude",),), 'bbox_bottom_right_lat' => array('#type' => 'textfield', '#element_validate' => array('adc_latitude_validate'), '#title' => t('Bottom right latitude'), '#attributes' => array('placeholder' => t('Bottom right latitude'), 'title' => "Bottom right latitude",),),);
    $form['geographical_search']['map'] = array('#prefix' => ' ', '#markup' => adc_get_geographical_search_map(), '#suffix' => ' ',);
    $form['#validate'][] = 'msb_all_or_none_latlon_validate';
    $form['#validate'][] = 'msb_foo_validate';
    $form['submit'] = array('#type' => 'submit', '#value' => t('Search'), '#submit' => array('metsis_qsearch_submit'),);
    if (FORM_RESET_VISIBLE) {
        $form['options']['reset'] = array('#type' => 'submit', '#value' => t('Reset'), '#submit' => array('metsis_qsearch_form_reset'),);
    } if (BASKET_ELEMENTS_VISIBLE) {
        global $user;
        if (($user->uid) && get_user_item_count($user->uid) > 0) {
            $form['goto_basket'] = array('#type' => 'submit', '#value' => t('Basket (@basket_item_count)', array('@basket_item_count' => get_user_item_count($user->uid))), '#submit' => array('adc_goto_basket'), '#validate' => array(), '#attributes' => array('class' => array('adc-button-small',),),);
            $form['empty_basket'] = array('#type' => 'submit', '#value' => t('Empty basket'), '#submit' => array('adc_empty_basket'), '#validate' => array(), '#attributes' => array('class' => array('adc-button-small',),),);
        }
    } $path = drupal_get_path('module', 'metsis_qsearch');
    $form['#attached'] = array('css' => array('type' => 'file', 'data' => $path . '/css/metsis_qsearch.css',), 'js' => array('type' => 'file', 'data' => $path . '/js/metsis_qsearch.js',),);
    return $form;
}

function metsis_qsearch_submit($form, &$form_state) {
    $initial_user_query = msb_get_final_solr_qq($form_state);
    $_SESSION['qsearch']['initial_user_query'] = $initial_user_query;
    $bytes = 32;
    $_SESSION['qsearch']['qcache_key'] = bin2hex(openssl_random_pseudo_bytes(32)) . (string) time();
    $form_state['redirect'] = array('results/', array('query' => array('page' => 1,),),);
}

function qsearch_results_page() {
    $params = drupal_get_query_parameters();
    return q_get_paged_form();
}

function theme_metsis_qsearch_form($variables) {
    global $metsis_conf;
    $form = $variables['form'];
    $output = '';
    $output .= '<div class="non-map-div">';
    $output .= '<div class="full-text-search-div">';
    $output .= drupal_render($form['full_text_search']);
    $output .= '</div>';
    if (COLLECTION_PERIOD_VISIBLE) {
        $output .= '<div class="data-collection-period-div">';
        $output .= drupal_render($form['data_collection_period']);
        $output .= '</div>';
    } $output .= '<div class="bounding-box-div">';
    $output .= drupal_render($form['bounding_box']);
    $output .= '</div>';
    if (INSTITUTIONS_VISIBLE) {
        $output .= '<div class="institutions-div">';
        $output .= drupal_render($form['institutions']);
        $output .= '</div>';
    } if (PLATFORM_LONG_NAME_VISIBLE) {
        $output .= '<div class="platform_long_name-div">';
        $output .= drupal_render($form['platform_long_name']);
        $output .= '</div>';
    } if ($metsis_conf['cloud_cover_value_visible'] === TRUE) {
        $output .= '<div class="cloud_cover_value-div">';
        $output .= drupal_render($form['cloud_cover_value']);
        $output .= '</div>';
    } if (INVESTIGATOR_VISIBLE) {
        $output .= '<div class="investigator-div">';
        $output .= drupal_render($form['investigator']);
        $output .= '</div>';
    } if (TOPICS_AND_VARIABLES_VISIBLE) {
        $output .= '<div class="topics-and-variables-div">';
        $output .= drupal_render($form['topics_and_variables']);
        $output .= '</div>';
    } $output .= '<div class="operational-status-div">';
    $output .= drupal_render($form['operational_status']);
    $output .= '</div>';
    $output .= '<div class="form-action-div">';
    $output .= drupal_render($form['submit']);
    $output .= drupal_render($form['options']['reset']);
    if (FORM_RESET_VISIBLE) {
        $output .= drupal_render($form['reset']);
    } if (BASKET_ELEMENTS_VISIBLE) {
        global $user;
        if (($user->uid) && get_user_item_count($user->uid) > 0) {
            $output .= drupal_render($form['goto_basket']);
            $output .= drupal_render($form['empty_basket']);
        }
    } $output .= '</div>';
    $output .= '</div>';
    $output .= '<div class="map-div">';
    $output .= '<div class="geographical-search-div">';
    $output .= drupal_render($form['geographical_search']);
    $output .= '</div>';
    $output .= '</div>';
    $output .= drupal_render_children($form);
    return $output;
}
