<?php

require_once drupal_get_path('module', 'metsis_qsearch') . '/metsis_qsearch.utils.inc';
require_once drupal_get_path('module', 'metsis_qsearch') . '/metsis_qsearch.constants.inc';

function q_get_paged_form() {
    return drupal_get_form('q_results_tabular_form');
}

function q_results_tabular_form($form, &$form_state) {
    $params = drupal_get_query_parameters();
    $form ['table'] = qsearch_get_ctable($form_state, $params['page']);
    $form['#validate'][] = 'adc_min_select_validate';
    $number_of_pages = $_SESSION['qsearch']['number_of_pages'];
    $pager_markup = '<div class="pagination-holder clearfix"><div id="light-pagination" class="pagination light-theme simple-pagination"></div><input type="hidden" name="number_of_pages" value="' . $number_of_pages . '"></div>';
    $form['pagination'] = array('#markup' => $pager_markup, '#prefix' => '<div id="number_of_pages_info_div">', '#suffix' => '</div>',);
    if (BASKET_ELEMENTS_VISIBLE) {
        if (count($form['table']['table']['#options']) > 0) {
            $form['add_to_basket'] = array('#weight' => 2, '#type' => 'submit', '#value' => t('Add to basket'), '#submit' => array('adc_add_to_basket'),);
        }
    } $bts_markup = '<a class="adc-button" href="/';
    $bts_markup .= METSIS_QSEARCH_ENDPOINT;
    $bts_markup .= '/?quid=';
    $bts_markup .= $_SESSION['qsearch']['quid'];
    $bts_markup .= '">Back to search</a>';
    $form['back_to_search'] = array('#weight' => 3, '#markup' => $bts_markup,);
    global $base_url;
    $ss_markup = '<div class="adc-button"> ';
    $ss_markup .= '<a href="mailto:?subject=Data search&body=';
    $ss_markup .= $base_url;
    $ss_markup .= '/';
    $ss_markup .= METSIS_QSEARCH_ENDPOINT;
    $ss_markup .= '/?quid=';
    $ss_markup .= $_SESSION['qsearch']['quid'];
    $ss_markup .= '">';
    $ss_markup .= SHARE_RESULTS_EMAIL_BUTTON_TEXT;
    $ss_markup .= '</a>';
    $ss_markup .= '</div>';
    $form['share_search'] = array('#weight' => 7, '#markup' => $ss_markup,);
    return $form;
}

function q_results_tabular_form_submit($form, &$form_state) {
    drupal_set_message(t('The form has been submitted.'));
}

function qsearch_get_ctable($form_state, $page_number) {
    global $metsis_conf;
    global $base_url;
    $number_results_per_page = RESULTS_PER_PAGE;
    $number_results_found = q_get_num_res_found($_SESSION['qsearch']['initial_user_query']);
    $number_of_pages = ceil($number_results_found / $number_results_per_page);
    $start_row = ($page_number - 1) * $number_results_per_page;
    $_SESSION['qsearch']['number_of_pages'] = $number_of_pages;
    $solr_obj = q_do_search($_SESSION['qsearch']['initial_user_query'], $number_results_per_page, $start_row);
    $show_children = FALSE;
    $children = NULL;
    $header = array();
    if (DATASETNAME_VISIBLE) {
        $header['datasetName'] = t('Dataset name');
    } if (INSTITUTIONS_VISIBLE) {
        $header['institutions'] = t('Institutions');
    } if (PLATFORM_LONG_NAME_VISIBLE) {
        $header['platform_long_name'] = t('Platform');
    } if ($metsis_conf['results_cloud_cover_value_visible'] === TRUE) {
        $header['cloud_cover_value'] = t('Cloud cover [%] of scene');
    } if (TITLE_VISIBLE) {
        $header['title'] = t('Title');
    } if (PROJECT_VISIBLE) {
        $header['project'] = t('Project');
    } if (KEYWORDS_VISIBLE) {
        $header['keywords'] = t('Keywords');
    } if (ABSTRACT_VISIBLE) {
        $header['abstract'] = t('Abstract');
    } if (COLLECTION_PERIOD_VISIBLE) {
        $header['collection_period'] = t('Collection period');
    } $options = array();
    if (!empty($solr_obj['error'])) {
        drupal_set_message($solr_obj['error']['msg'], 'error');
        return;
    } if (!empty($solr_obj['response']['numFound'])) {
        drupal_set_message("<b>" . t("Number of datasets found") . ": " . $solr_obj['response']['numFound'] . "</b>", 'status', FALSE);
    } foreach ($solr_obj['response']['docs'] as $doc) {
        if (key_exists(METADATA_PREFIX . 'related_dataset', $doc)) {
            $child_count = count($doc[METADATA_PREFIX . 'related_dataset']);
            if ($child_count > 0) {
                
            }
        } if (isset($doc[METADATA_PREFIX . 'data_access']['type']['OGC_WMS']['url']) && (!empty($doc[METADATA_PREFIX . 'data_access']['type']['OGC_WMS']['url']) )) {
            $getcapdoc = $doc[METADATA_PREFIX . 'data_access']['type']['OGC_WMS']['url'];
            $target_url = $base_url . '/' . 'metsis/map/wms?dataset=' . $doc[METADATA_PREFIX . 'metadata_identifier'] . '&solr_core=' . SOLR_CORE_PARENT;
            $tmp_thumb_response = msb_get_fields(SOLR_CORE_MAP_THUMBNAILS, $doc[METADATA_PREFIX . 'metadata_identifier'], array("thumbnail_data", "thumbnail", "base_map"));
            if (count($tmp_thumb_response['response']['docs']) > 0) {
                if (isset($tmp_thumb_response['response']['docs'][0]['thumbnail_data'])) {
                    if ($tmp_thumb_response['response']['docs'][0]['thumbnail_data'] != "") {
                        $thumbnail_data = $tmp_thumb_response['response']['docs'][0]['thumbnail_data'];
                        $mapthumb = adc_get_map_thumb_divs($thumbnail_data, $target_url);
                    }
                    else {
                        $thumbnail_data = "/" . variable_get('file_public_path', conf_path() . '/files') . "/" . "missing_map_place_holder.png";
                        $mapthumb = adc_get_map_thumb_divs($thumbnail_data, $target_url);
                    }
                }
            }
            else {
                if (defined('SITE_STATUS')) {
                    if (SITE_STATUS === 'dev' || SITE_STATUS === 'test') {
                        drupal_set_message("No thumbnail URL is indexed for <b>" . $doc[METADATA_PREFIX . 'metadata_identifier'] . "</b>", 'warning');
                    }
                } $thumbnail_data = NULL;
            }
        }
        else {
            $mapthumb = NULL;
            $children = $show_children ? msb_get_children($doc[METADATA_PREFIX . 'metadata_identifier']) : NULL;
        } $metadata_page_link = msb_get_md_links('l1', $doc[METADATA_PREFIX . 'metadata_identifier']);
        if (h_adc_has_data_access_type($doc, "OPeNDAP") == 1) {
            if (adc_has_feature_type($doc[METADATA_PREFIX . 'metadata_identifier'], "timeSeries") === 1) {
                $fimex_link = "";
            }
            else {
                $fimex_link = msb_get_fimex_link($doc[METADATA_PREFIX . 'metadata_identifier'], SOLR_CORE_PARENT);
            }
        }
        else {
            $fimex_link = "";
        } if (DATASETNAME_VISIBLE) {
            $dataset_name = $doc[METADATA_PREFIX . 'metadata_identifier'];
        } if (INSTITUTIONS_VISIBLE) {
            $institutions = !empty($doc[METADATA_PREFIX . 'personnel_organisation'][0]) ? $doc[METADATA_PREFIX . 'personnel_organisation'][0] : ' ';
        } if (PLATFORM_LONG_NAME_VISIBLE) {
            $platform_long_name = !empty($doc[METADATA_PREFIX . 'platform_long_name'][0]) ? $doc[METADATA_PREFIX . 'platform_long_name'][0] : ' ';
        } global $metsis_conf;
        if ($metsis_conf['results_cloud_cover_value_visible'] === TRUE) {
            if (array_key_exists(METADATA_PREFIX . "cloud_cover_value", $doc)) {
                $cloud_cover_value = $doc[METADATA_PREFIX . 'cloud_cover_value'];
            }
            else {
                $cloud_cover_value = NULL;
            }
        } if (TITLE_VISIBLE) {
            $title = $doc[METADATA_PREFIX . 'title'][0];
        } if (PROJECT_VISIBLE) {
            $project = key_exists(METADATA_PREFIX . 'project_long_name', $doc) ? $doc[METADATA_PREFIX . 'project_long_name'] : "";
        } if (KEYWORDS_VISIBLE) {
            $keywords = adc_keywords_to_string($doc[METADATA_PREFIX . 'keywords_keyword']);
        } if (ABSTRACT_VISIBLE) {
            if (isset($doc[METADATA_PREFIX . 'abstract'])) {
                $abstract = $doc[METADATA_PREFIX . 'abstract'][0];
            }
            else {
                $abstract = '';
            }
        } $temporal_extent_start_date = !empty($doc[METADATA_PREFIX . 'temporal_extent_start_date']) ? $doc[METADATA_PREFIX . 'temporal_extent_start_date'] : '';
        $temporal_extent_end_date = !empty($doc[METADATA_PREFIX . 'temporal_extent_end_date']) ? $doc[METADATA_PREFIX . 'temporal_extent_end_date'] : '';
        if (COLLECTION_PERIOD_VISIBLE) {
            if ((RESULTS_DATE_DISPLAY_FORMAT != '') && $temporal_extent_start_date != '' && $temporal_extent_end_date != '') {
                $collection_period = get_metsis_date($temporal_extent_start_date, RESULTS_DATE_DISPLAY_FORMAT) . " to " . get_metsis_date($temporal_extent_end_date, RESULTS_DATE_DISPLAY_FORMAT);
            }
            else {
                $collection_period = $temporal_extent_start_date . " to " . $temporal_extent_end_date;
            }
        } $visualize_button = "";
        $ascii_button = "";
        $leveltwo_button = adc_get_leveltwo_links($doc[METADATA_PREFIX . 'metadata_identifier']);
        if (adc_has_feature_type($doc[METADATA_PREFIX . 'metadata_identifier'], "timeSeries") === 1) {
            $this_url = url(current_path(), array('absolute' => TRUE, 'query' => drupal_get_query_parameters()));
            $visualize_url = "/" . TS_ENDPOINT . "?metadata_identifier=" . $doc[METADATA_PREFIX . 'metadata_identifier'];
            $visualize_url .= "&calling_results_page=" . $this_url;
            $ascii_url = "/" . CSV_ENDPOINT . "?metadata_identifier=" . $doc[METADATA_PREFIX . 'metadata_identifier'];
            $ascii_url .= "&calling_results_page=" . $this_url;
            $visualize_button = get_data_access_markup(TS_VISUALIZATION_BUTTON_TEXT, $visualize_url);
            $ascii_button = get_data_access_markup(CSV_BUTTON_TEXT, $ascii_url);
        } if (isset($doc[METADATA_PREFIX . 'related_information_resource'])) {
            $rir = adc_parse_related_information_resource($doc[METADATA_PREFIX . 'related_information_resource']);
            if (isset($rir['Dataset landing page']['uri'])) {
                $data_access_HTTP = adc_get_button_markup($doc[METADATA_PREFIX . 'title'][0], $rir['Dataset landing page']['uri']);
            }
            else {
                $data_access_HTTP = $doc[METADATA_PREFIX . 'title'][0];
            }
        }
        elseif (isset($doc[METADATA_PREFIX . 'data_access']['type']['HTTP']['url'])) {
            $data_access_HTTP = get_data_access_markup($doc[METADATA_PREFIX . 'title'][0], $doc[METADATA_PREFIX . 'data_access']['type']['HTTP']['url']);
        }
        else {
            $data_access_HTTP = $doc[METADATA_PREFIX . 'title'][0];
        } if (isset($doc[METADATA_PREFIX . 'data_access']['type']['OGC_WMS']['url'])) {
            $data_access_OGC_WMS = $doc[METADATA_PREFIX . 'data_access']['type']['OGC_WMS']['url'];
        }
        else {
            $data_access_OGC_WMS = "metadata_not_found";
        } $dar = [];
        if (isset($doc[METADATA_PREFIX . 'data_access_resource'])) {
            $dar = adc_parse_solr_mmd_type_one($doc[METADATA_PREFIX . 'data_access_resource']);
        } if (isset($dar['"HTTP"'])) {
            if (defined('DAR_HTTP_BUTTON_TEXT')) {
                $netcdf_download = get_data_access_markup(DAR_HTTP_BUTTON_TEXT, $dar['"HTTP"']);
            }
            else {
                $netcdf_download = get_data_access_markup('Download .NetCDF product', $dar['"HTTP"']);
            }
        }
        else {
            $netcdf_download = "";
        } if (isset($dar['"ODATA"'])) {
            if (defined('DAR_ODATA_BUTTON_TEXT')) {
                $odata_download = get_data_access_markup(DAR_ODATA_BUTTON_TEXT, $dar['"ODATA"']);
            }
            else {
                $odata_download = get_data_access_markup('Download .SAFE product', $dar['"ODATA"']);
            }
        }
        else {
            $odata_download = "";
        } $options[$dataset_name] = array();
        if (DATASETNAME_VISIBLE) {
            if (!isset($mapthumb)) {
                $mapthumb = "";
            } $options[$dataset_name]['datasetName'] = t($data_access_HTTP . $netcdf_download . $odata_download . $leveltwo_button . $metadata_page_link . $fimex_link . $visualize_button . $ascii_button . $mapthumb);
        } if (INSTITUTIONS_VISIBLE) {
            $options[$dataset_name]['institutions'] = t($institutions);
        } if (PLATFORM_LONG_NAME_VISIBLE) {
            $options[$dataset_name]['platform_long_name'] = t($platform_long_name);
        } global $metsis_conf;
        if ($metsis_conf['results_cloud_cover_value_visible'] === TRUE) {
            if ($cloud_cover_value != NULL) {
                $options[$dataset_name]['cloud_cover_value'] = t(number_format($cloud_cover_value, ($metsis_conf['results_number_decimal_display_format'])));
            }
            else {
                $options[$dataset_name]['cloud_cover_value'] = t($cloud_cover_value);
            }
        } if (TITLE_VISIBLE) {
            $options[$dataset_name]['title'] = t($title);
        } if (PROJECT_VISIBLE) {
            if ($project != "") {
                $options[$dataset_name]['project'] = t($project);
            }
            else {
                $options[$dataset_name]['project'] = "";
            }
        } if (KEYWORDS_VISIBLE) {
            $options[$dataset_name]['keywords'] = t($keywords);
        } if (ABSTRACT_VISIBLE) {
            $options[$dataset_name]['abstract'] = t($abstract);
        } $options[$dataset_name]['collection_period'] = t($collection_period);
        if ($show_children) {
            $max_count = 10;
            $count = ($children['response']['numFound'] > $max_count) ? $max_count : $children['response']['numFound'];
            for ($i = 0; $i < $count; $i++) {
                
            } for ($i = 0; $i < $count; $i++) {
                $child = $children['response']['docs'][$i];
                $dataset_name = $child[METADATA_PREFIX . 'metadata_identifier'];
                $temporal_extent_start_date = !empty($child[METADATA_PREFIX . 'temporal_extent_start_date']) ? $child[METADATA_PREFIX . 'temporal_extent_start_date'] : ' ';
                $temporal_extent_end_date = !empty($child[METADATA_PREFIX . 'temporal_extent_end_date']) ? $child[METADATA_PREFIX . 'temporal_extent_end_date'] : ' ';
                $collection_period = msb_get_short_isodate($temporal_extent_start_date) . " to " . msb_get_short_isodate($temporal_extent_end_date);
                if (isset($child[METADATA_PREFIX . 'data_access_resource'])) {
                    $data_access_HTTP = $child[METADATA_PREFIX . 'data_access_resource'];
                }
                else {
                    $data_access_HTTP = "Metadata item 'data_access_resource' was not found!";
                } $options[$dataset_name] = array('datasetName' => l(t($dataset_name), $data_access_HTTP, array('external' => TRUE, 'html' => TRUE, 'attributes' => array('class' => array('ext_data_source'),))), 'institutions' => '', 'title' => '', 'project' => '', 'keywords' => '', 'abstract' => '', 'collection_period' => t($collection_period), '#attributes' => array('class' => array('childdataset',)));
            }
        }
    } if (BASKET_ELEMENTS_VISIBLE) {
        $form ['table'] = array('#header' => $header, '#type' => 'tableselect', '#sticky' => false, '#options' => $options, '#empty' => t('No datasets found.'), '#weight' => 0, '#multiple' => true, '#tree' => TRUE, '#attributes' => array('class' => array('datasets', 'sortable',)));
    }
    else {
        $form ['table'] = array('#header' => $header, '#theme' => 'table', '#rows' => $options, '#empty' => t('No datasets found.'), '#weight' => 0, '#attributes' => array('class' => array('datasets', 'sortable',)));
    } $path = drupal_get_path('module', 'metsis_qsearch');
    $form['#attached'] = array('css' => array('type' => 'file', 'data' => $path . '/css/simplePagination.css',), 'js' => array('type' => 'file', 'data' => $path . '/js/jquery.simplePagination.js',),);
    return $form;
}