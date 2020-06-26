<?php

require_once drupal_get_path('module', 'metsis_lib') . '/includes/metsis_lib.utils.inc';
global $metsis_conf;
define('METSIS_QSEARCH_ENDPOINT', $metsis_conf['metsis_qsearch_endpoint']);
if (isset($metsis_conf['dar_http_button_text'])) {
  define('DAR_HTTP_BUTTON_TEXT', $metsis_conf['dar_http_button_text']);
}
if (isset($metsis_conf['dar_odata_button_text'])) {
  define('DAR_ODATA_BUTTON_TEXT', $metsis_conf['dar_odata_button_text']);
}
if (isset($metsis_conf['dar_opendap_button_text'])) {
  define('DAR_OPENDAP_BUTTON_TEXT', $metsis_conf['dar_opendap_button_text']);
}
if (isset($metsis_conf['dar_ogc_wms_button_text'])) {
  define('DAR_OGC_WMS_BUTTON_TEXT', $metsis_conf['dar_ogc_wms_button_text']);
}
if (isset($metsis_conf['solr_metadata_button_text'])) {
  define('SOLR_METADATA_BUTTON_TEXT', $metsis_conf['solr_metadata_button_text']);
}
if (isset($metsis_conf['show_metadata_inline']) && $metsis_conf['show_metadata_inline'] === TRUE) {
  define('SHOW_METADATA_INLINE', $metsis_conf['show_metadata_inline']);
}

define('TS_VISUALIZATION_BUTTON_TEXT', $metsis_conf['ts_visualization_button_text']);
define('CSV_BUTTON_TEXT', $metsis_conf['csv_button_text']);
if (isset($metsis_conf['landing_page_button_var'])) {
  define('LANDING_PAGE_BUTTON_VAR', $metsis_conf['landing_page_button_var']);
}
define('SOLR_SERVER_IP', $metsis_conf['solr_server_ip']);
define('SOLR_SERVER_PORT', $metsis_conf['solr_server_port']);
define('BASKET_ELEMENTS_VISIBLE', $metsis_conf['basket_elements_visible']);
define('FORM_RESET_VISIBLE', $metsis_conf['form_reset_visible']);
define('TOPICS_AND_VARIABLES_VISIBLE', $metsis_conf['topics_and_variables_visible']);
define('TOPICS_AND_VARIABLES_INITIALLY_COLLAPSED', $metsis_conf['topics_and_variables_initially_collapsed']);
define('INSTITUTIONS_VISIBLE', $metsis_conf['institutions_visible']);
if (isset($metsis_conf['results_institutions_visible'])) {
  define('RESULTS_INSTITUTIONS_VISIBLE', $metsis_conf['results_institutions_visible']);
}
define('INSTITUTIONS_INITIALLY_COLLAPSED', $metsis_conf['institutions_initially_collapsed']);
if (isset($metsis_conf['collections'])) {
  define('COLLECTIONS', adc_array_to_solror(array_map('trim', explode(',', $metsis_conf['collections']))));
}
define('COLLECTIONS_VISIBLE', $metsis_conf['collections_visible']);
define('COLLECTIONS_INITIALLY_COLLAPSED', $metsis_conf['collections_initially_collapsed']);
if (isset($metsis_conf['label_collections'])) {
  define('LABEL_COLLECTIONS', $metsis_conf['label_collections']);
}
define('INVESTIGATOR_VISIBLE', $metsis_conf['investigator_visible']);
define('INVESTIGATOR_INITIALLY_COLLAPSED', $metsis_conf['investigator_initially_collapsed']);
if (isset($metsis_conf['results_investigator_visible'])) {
  define('RESULTS_INVESTIGATOR_VISIBLE', $metsis_conf['results_investigator_visible']);
}
if (isset($metsis_conf['results_abstract_visible'])) {
  define('RESULTS_ABSTRACT_VISIBLE', $metsis_conf['results_abstract_visible']);
}
if (isset($metsis_conf['results_collection_period_visible'])) {
  define('RESULTS_COLLECTION_PERIOD_VISIBLE', $metsis_conf['results_collection_period_visible']);
}
define('DATASETNAME_VISIBLE', $metsis_conf['datasetName_visible']);
define('TITLE_VISIBLE', $metsis_conf['title_visible']);
define('PROJECT_VISIBLE', $metsis_conf['project_visible']);
if (isset($metsis_conf['results_keywords_visible'])) {
  define('RESULTS_KEYWORDS_VISIBLE', $metsis_conf['results_keywords_visible']);
}

if (isset($metsis_conf['results_cloud_cover_value_visible'])) {
  define('RESULTS_CLOUD_COVER_VALUE_VISIBLE', $metsis_conf['results_cloud_cover_value_visible']);
}
define('COLLECTION_PERIOD_VISIBLE', $metsis_conf['collection_period_visible']);
define('DATA_COLLECTION_PERIOD_INITIALLY_COLLAPSED', $metsis_conf['data_collection_period_initially_collapsed']);
define('PLATFORM_LONG_NAME_VISIBLE', $metsis_conf['platform_long_name_visible']);
if (isset($metsis_conf['results_platform_long_name_visible'])) {
  define('RESULTS_PLATFORM_LONG_NAME_VISIBLE', $metsis_conf['results_platform_long_name_visible']);
}
define('PLATFORM_LONG_NAME_INITIALLY_COLLAPSED', $metsis_conf['platform_long_name_initially_collapsed']);
define('CLOUD_COVER_VALUE_INITIALLY_COLLAPSED', $metsis_conf['cloud_cover_value_initially_collapsed']);
define('RESULTS_DATE_DISPLAY_FORMAT', $metsis_conf['results_date_display_format']);
define('SEARCH_MAXIMUM_ROWS_TO_FETCH', $metsis_conf['search_maximum_rows_to_fetch']);
define('PINS_ON_RES_MAP', $metsis_conf['pins_on_res_map']);
define('INIT_PROJ_RES_MAP', $metsis_conf['init_proj_res_map']);
define('ADDITIONAL_LAYERS', $metsis_conf['additional_layers']);
if (isset($metsis_conf['search_max_metadata_age'])) {
  define('SEARCH_MAX_METADATA_AGE', $metsis_conf['search_max_metadata_age']);
}
define('BOUNDING_BOX_INITIALLY_COLLAPSED', $metsis_conf['bounding_box_initially_collapsed']);
define('FULL_TEXT_SEARCH_INITIALLY_COLLAPSED', $metsis_conf['full_text_search_initially_collapsed']);
if (isset($metsis_conf['instrument_polarisation_visible'])) {
  define('INSTRUMENT_POLARISATION_VISIBLE', $metsis_conf['instrument_polarisation_visible']);
}
if (isset($metsis_conf['instrument_polarisation_initially_collapsed'])) {
  define('INSTRUMENT_POLARISATION_INITIALLY_COLLAPSED', $metsis_conf['instrument_polarisation_initially_collapsed']);
}
if (isset($metsis_conf['instrument_polarisations'])) {
  define('INSTRUMENT_POLARISATIONS', $metsis_conf['instrument_polarisations']);
}
if (isset($metsis_conf['product_types'])) {
  define('PRODUCT_TYPES', $metsis_conf['product_types']);
}
if (isset($metsis_conf['product_types_visible'])) {
  define('PRODUCT_TYPES_VISIBLE', $metsis_conf['product_types_visible']);
}
if (isset($metsis_conf['instrument_modes'])) {
  define('INSTRUMENT_MODES', $metsis_conf['instrument_modes']);
}
if (isset($metsis_conf['instrument_modes_visible'])) {
  define('INSTRUMENT_MODES_VISIBLE', $metsis_conf['instrument_modes_visible']);
}
define('ICONS_PATH', conf_path() . '/files/icons');
define('SHARE_RESULTS_EMAIL_BUTTON_VISIBLE', $metsis_conf['share_results_email_button_visible']);
define('SHARE_RESULTS_EMAIL_BUTTON_TEXT', $metsis_conf['share_results_email_button_text']);
define('SHARE_RESULTS_COPY_BUTTON_VISIBLE', $metsis_conf['share_results_copy_button_visible']);
define('SHARE_RESULTS_COPY_BUTTON_TEXT', $metsis_conf['share_results_copy_button_text']);
define('RESULTS_PER_PAGE', $metsis_conf['results_per_page']);
//define('DEBUG', FALSE);
define('CAPDOC_POSTFIX', $metsis_conf['capdoc_postfix']);
define('MISSING_METADATA', $metsis_conf['missing_metadata']);
define('NOT_APPLICABLE_METADATA', $metsis_conf['not_applicable_metadata']);
define('MISSING_OPENDAP', $metsis_conf['missing_opendap']);
define('MISSING_DATA_ACCESS_RESOURCE', $metsis_conf['missing_data_access_resource']);


/**
 * labels, hints and placeholders
 */
if (isset($metsis_conf['label_bounding_box'])) {
  define('LABEL_BOUNDING_BOX', $metsis_conf['label_bounding_box']);
}
if (isset($metsis_conf['hint_top_left_longitude'])) {
  define('HINT_TOP_LEFT_LONGITUDE', $metsis_conf['hint_top_left_longitude']);
}
if (isset($metsis_conf['placeholder_top_left_longitude'])) {
  define('PLACEHOLDER_TOP_LEFT_LONGITUDE', $metsis_conf['placeholder_top_left_longitude']);
}
if (isset($metsis_conf['hint_top_left_latitude'])) {
  define('HINT_TOP_LEFT_LATITUDE', $metsis_conf['hint_top_left_latitude']);
}
if (isset($metsis_conf['placeholder_top_left_latitude'])) {
  define('PLACEHOLDER_TOP_LEFT_LATITUDE', $metsis_conf['placeholder_top_left_latitude']);
}
if (isset($metsis_conf['hint_bottom_right_longitude'])) {
  define('HINT_BOTTOM_RIGHT_LONGITUDE', $metsis_conf['hint_bottom_right_longitude']);
}
if (isset($metsis_conf['placeholder_bottom_right_longitude'])) {
  define('PLACEHOLDER_BOTTOM_RIGHT_LONGITUDE', $metsis_conf['placeholder_bottom_right_longitude']);
}
if (isset($metsis_conf['hint_bottom_right_latitude'])) {
  define('HINT_BOTTOM_RIGHT_LATITUDE', $metsis_conf['hint_bottom_right_latitude']);
}
if (isset($metsis_conf['placeholder_bottom_right_latitude'])) {
  define('PLACEHOLDER_BOTTOM_RIGHT_LATITUDE', $metsis_conf['placeholder_bottom_right_latitude']);
}

if (isset($metsis_conf['label_geographic_extent'])) {
  define('LABEL_GEOGRAPHIC_EXTENT', $metsis_conf['label_geographic_extent']);
}

if (isset($metsis_conf['label_temporal_extent'])) {
  define('LABEL_TEMPORAL_EXTENT', $metsis_conf['label_temporal_extent']);
}
if (isset($metsis_conf['hint_temporal_extent_start_date'])) {
  define('HINT_TEMPORAL_EXTENT_START_DATE', $metsis_conf['hint_temporal_extent_start_date']);
}
if (isset($metsis_conf['placeholder_temporal_extent_start_date'])) {
  define('PLACEHOLDER_TEMPORAL_EXTENT_START_DATE', $metsis_conf['placeholder_temporal_extent_start_date']);
}
if (isset($metsis_conf['hint_temporal_extent_end_date'])) {
  define('HINT_TEMPORAL_EXTENT_END_DATE', $metsis_conf['hint_temporal_extent_end_date']);
}
if (isset($metsis_conf['placeholder_temporal_extent_end_date'])) {
  define('PLACEHOLDER_TEMPORAL_EXTENT_END_DATE', $metsis_conf['placeholder_temporal_extent_end_date']);
}
if (isset($metsis_conf['label_full_text'])) {
  define('LABEL_FULL_TEXT', $metsis_conf['label_full_text']);
}
if (isset($metsis_conf['hint_full_text'])) {
  define('HINT_FULL_TEXT', $metsis_conf['hint_full_text']);
}
if (isset($metsis_conf['placeholder_full_text'])) {
  define('PLACEHOLDER_FULL_TEXT', $metsis_conf['placeholder_full_text']);
}
if (isset($metsis_conf['label_institutions'])) {
  define('LABEL_INSTITUTIONS', $metsis_conf['label_institutions']);
}
if (isset($metsis_conf['label_platform_long_name'])) {
  define('LABEL_PLATFORM_LONG_NAME', $metsis_conf['label_platform_long_name']);
}
if (isset($metsis_conf['label_investigator'])) {
  define('LABEL_INVESTIGATOR', $metsis_conf['label_investigator']);
}
if (isset($metsis_conf['hint_investigator'])) {
  define('HINT_INVESTIGATOR', $metsis_conf['hint_investigator']);
}
if (isset($metsis_conf['placeholder_investigator'])) {
  define('PLACEHOLDER_INVESTIGATOR', $metsis_conf['placeholder_investigator']);
}

if (isset($metsis_conf['label_topics_and_variables'])) {
  define('LABEL_TOPICS_AND_VARIABLES', $metsis_conf['label_topics_and_variables']);
}
if (isset($metsis_conf['hint_topics_and_variables'])) {
  define('HINT_TOPICS_AND_VARIABLES', $metsis_conf['hint_topics_and_variables']);
}
if (isset($metsis_conf['placeholder_topics_and_variables'])) {
  define('PLACEHOLDER_TOPICS_AND_VARIABLES', $metsis_conf['placeholder_topics_and_variables']);
}


if (isset($metsis_conf['results_thumb_column_header'])) {
  define('RESULTS_THUMB_COLUMN_HEADER', $metsis_conf['results_thumb_column_header']);
}
