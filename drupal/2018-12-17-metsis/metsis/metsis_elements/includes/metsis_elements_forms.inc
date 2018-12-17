<?php

function elements_tabular_form($form, &$form_state) {
    global $metsis_conf;
    global $base_url;
    $params = drupal_get_query_parameters();
    $page_number = $params['page'];
    $metadata_identifier = $params['metadata_identifier'];
    $solr_parent = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_PARENT, array($metadata_identifier), array('mmd_related_dataset'), 0, 1);
    $metadata_identifier_array = $solr_parent['response']['docs'][0]['mmd_related_dataset'];
    $number_of_children = count($metadata_identifier_array);
    $number_results_per_page = RESULTS_PER_PAGE;
    $number_results_found = $number_of_children;
    $number_of_pages = ceil($number_results_found / $number_results_per_page);
    $start_row = ($page_number - 1) * $number_results_per_page;
    $_SESSION['elements']['number_of_pages'] = $number_of_pages;
    $fields_array = explode(',', REQUIRED_CHILD_METADATA);
    $solr_obj = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_CHILD, $metadata_identifier_array, $fields_array, $start_row, $number_results_per_page);
    $rso = reshape_solr_obj($solr_obj);
    $rsok = array_keys($rso);
    $thumbnails = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_MAP_THUMBNAILS, $rsok, array(METADATA_PREFIX . 'metadata_identifier', 'thumbnail_data'), 0, 1000000000);
    $thumbnail_data_array = [];
    foreach ($thumbnails['response']['docs'] as $doc) {
        $thumbnail_data_array[$doc[METADATA_PREFIX . 'metadata_identifier']] = $doc['thumbnail_data'];
    } $pager_markup = '<div class="pagination-holder clearfix"><div id="light-pagination" class="pagination light-theme simple-pagination"></div><input type="hidden" name="number_of_pages" value="' . $number_of_pages . '"></div>';
    $form['pagination'] = array('#weight' => 20, '#markup' => $pager_markup, '#prefix' => '<div id="number_of_pages_info_div">', '#suffix' => '</div>',);
    $header = array();
    if (DATASETNAME_VISIBLE) {
        $header['dataset_name'] = t('Dataset name');
    } if (in_array(METADATA_PREFIX . 'personnel_name', $fields_array)) {
        $header['personnel_name'] = t('PI');
    } if (in_array(METADATA_PREFIX . 'temporal_extent_start_date', $fields_array)) {
        $header['collection_period'] = t('Collection period');
    } foreach (array_keys($rso) as $mi) {
        $dar = adc_parse_data_access_resource($rso[$mi][METADATA_PREFIX . 'data_access_resource']);
        if (isset($dar['HTTP'])) {
            $dar_http = $dar['HTTP']['uri'];
        }
        else {
            $dar_http = '';
        } if (isset($dar['OPeNDAP'])) {
            $dar_opendap = $dar['OPeNDAP']['uri'];
            $dar_opendap .= '.html';
        }
        else {
            $dar_opendap = '';
        } if (isset($dar['OGC WMS'])) {
            $dar_ogc_wms = $dar['OGC WMS']['uri'];
        }
        else {
            $dar_ogc_wms = '';
        } $md_kv = adc_get_md_kv('l2', $mi);
        $title_kv = adc_get_child_title_kv($rso, $mi);
        if (isset($thumbnail_data_array[$mi])) {
            $thumbnail_data = $thumbnail_data_array[$mi];
        }
        else {
            $thumbnail_data = '';
        } $collection_period = trim($rso[$mi][METADATA_PREFIX . 'temporal_extent_start_date'] . ' to ' . $rso[$mi][METADATA_PREFIX . 'temporal_extent_end_date']);
        $personnel_name = trim($rso[$mi][METADATA_PREFIX . 'personnel_name'][0]);
        $dataset_name = adc_get_link_list(array($title_kv['href'], $md_kv['href']), array($title_kv['title'][0], $md_kv['display_text']));
        if ($dar_http != '') {
            $dataset_name = adc_get_link_list(array($title_kv['href'], $dar_http, $md_kv['href'],), array($title_kv['title'][0], DAR_HTTP_BUTTON_TEXT, $md_kv['display_text'],));
        } if ($dar_opendap != '') {
            $dataset_name = adc_get_link_list(array($title_kv['href'], $dar_http, $dar_opendap, $md_kv['href'],), array($title_kv['title'][0], DAR_HTTP_BUTTON_TEXT, DAR_OPENDAP_BUTTON_TEXT, $md_kv['display_text'],));
        } if ($dar_ogc_wms != '') {
            $dataset_name = adc_get_link_list(array($title_kv['href'], $dar_http, $dar_opendap, $md_kv['href'], $base_url . '/' . 'metsis/map/wms?dataset=' . $mi . '&solr_core=' . SOLR_CORE_CHILD), array($title_kv['title'][0], DAR_HTTP_BUTTON_TEXT, DAR_OPENDAP_BUTTON_TEXT, $md_kv['display_text'], '<img src="' . $thumbnail_data . '" alt="OGC WMS"'));
        } $options[$mi] = array('dataset_name' => $dataset_name, 'personnel_name' => $personnel_name, 'collection_period' => $collection_period,);
    } $form['table'] = array('#type' => 'tableselect', '#header' => $header, '#options' => $options, '#attributes' => array('class' => array('elements-vars-table')),);
    if (BASKET_ELEMENTS_VISIBLE) {
        if (count($form['table']['#options']) > 0) {
            $form['add_to_basket'] = array('#weight' => 30, '#type' => 'submit', '#value' => t('Add to basket'), '#submit' => array('adc_elements_add_to_basket'),);
        }
    } $path = drupal_get_path('module', 'metsis_qsearch');
    $form['#attached'] = array('css' => array('type' => 'file', 'data' => $path . '/css/simplePagination.css',), 'js' => array('type' => 'file', 'data' => $path . '/js/jquery.simplePagination.js',),);
    return $form;
}

function metsis_elements_submit($form, &$form_state) {
    $form_state["rebuild"] = TRUE;
}

function reshape_solr_obj($solr_obj) {
    $solr_obj_reshaped = [];
    foreach ($solr_obj['response']['docs'] as $sodoc) {
        foreach ($sodoc as $k => $v) {
            if ($k !== METADATA_PREFIX . 'metadata_identifier') {
                $solr_obj_reshaped[$sodoc[METADATA_PREFIX . 'metadata_identifier']][$k] = $v;
            }
        }
    } return $solr_obj_reshaped;
}

function adc_get_child_title_kv($solr_obj, $metadata_identifier) {
    $so = $solr_obj;
    $mi = $metadata_identifier;
    $title_kv = [];
    $title_kv['title'] = $so[$mi][METADATA_PREFIX . 'title'];
    if (isset($so[$mi][METADATA_PREFIX . 'related_information_resource'])) {
        $rir_kv = adc_get_rir_dar_kv($mi, $so[$mi][METADATA_PREFIX . 'related_information_resource']);
        $title_kv['href'] = $rir_kv['Dataset landing page']['uri'];
    }
    elseif (isset($so[$mi][METADATA_PREFIX . 'data_access_resource'])) {
        $dar_kv = adc_get_rir_dar_kv($mi, $so[$mi][METADATA_PREFIX . 'data_access_resource']);
        $title_kv['href'] = $dar_kv['HTTP']['uri'];
    }
    else {
        $title_kv['href'] = "";
    } return($title_kv);
}
