<?php

require_once drupal_get_path('module', 'metsis_lib') . '/includes/metsis_lib.utils.inc';

global $metsis_conf;

define('METSIS_QSEARCH_ENDPOINT', $metsis_conf['metsis_qsearch_endpoint']);

//define('RESULTS_PER_PAGE', $metsis_conf['results_per_page']);
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

define('TS_VISUALIZATION_BUTTON_TEXT', $metsis_conf['ts_visualization_button_text']);
define('CSV_BUTTON_TEXT', $metsis_conf['csv_button_text']);


/**
 * solr{
 */
define('SOLR_SERVER_IP', $metsis_conf['solr_server_ip']);
define('SOLR_SERVER_PORT', $metsis_conf['solr_server_port']);
//define('SOLR_SERVER_SSL_PORT', $metsis_conf['solr_server_ssl_port']);
/**
 * solr}
 */
/*
 * metsis web interface elements{
 */
//These will be moved to admin interface as options
define('BASKET_ELEMENTS_VISIBLE', $metsis_conf['basket_elements_visible']);
define('FORM_RESET_VISIBLE', $metsis_conf['form_reset_visible']);
define('TOPICS_AND_VARIABLES_VISIBLE', $metsis_conf['topics_and_variables_visible']);
define('TOPICS_AND_VARIABLES_INITIALLY_COLLAPSED', $metsis_conf['topics_and_variables_initially_collapsed']);
define('INSTITUTIONS_VISIBLE', $metsis_conf['institutions_visible']);
define('INSTITUTIONS_INITIALLY_COLLAPSED', $metsis_conf['institutions_initially_collapsed']);

if (isset($metsis_conf['collections'])) {
    define('COLLECTIONS', adc_array_to_solror(array_map('trim', explode(',', $metsis_conf['collections']))));
}
define('COLLECTIONS_VISIBLE', $metsis_conf['collections_visible']);
define('COLLECTIONS_INITIALLY_COLLAPSED', $metsis_conf['collections_initially_collapsed']);
define('INVESTIGATOR_VISIBLE', $metsis_conf['investigator_visible']);
define('INVESTIGATOR_INITIALLY_COLLAPSED', $metsis_conf['investigator_initially_collapsed']);
define('ABSTRACT_VISIBLE', $metsis_conf['abstract_visible']);
define('DATASETNAME_VISIBLE', $metsis_conf['datasetName_visible']);
define('TITLE_VISIBLE', $metsis_conf['title_visible']);
define('PROJECT_VISIBLE', $metsis_conf['project_visible']);
define('KEYWORDS_VISIBLE', $metsis_conf['keywords_visible']);
define('COLLECTION_PERIOD_VISIBLE', $metsis_conf['collection_period_visible']);
define('DATA_COLLECTION_PERIOD_INITIALLY_COLLAPSED', $metsis_conf['data_collection_period_initially_collapsed']);
define('PLATFORM_LONG_NAME_VISIBLE', $metsis_conf['platform_long_name_visible']);
define('PLATFORM_LONG_NAME_INITIALLY_COLLAPSED', $metsis_conf['platform_long_name_initially_collapsed']);
//define('CLOUD_COVER_VALUE_VISIBLE', $metsis_conf['cloud_cover_value_visible']);
define('CLOUD_COVER_VALUE_INITIALLY_COLLAPSED', $metsis_conf['cloud_cover_value_initially_collapsed']);
define('RESULTS_DATE_DISPLAY_FORMAT', $metsis_conf['results_date_display_format']);
//absolute maximum number of rows to fetch/display
define('SEARCH_MAXIMUM_ROWS_TO_FETCH', $metsis_conf['search_maximum_rows_to_fetch']);
if (isset($metsis_conf['search_max_metadata_age'])) {
    define('SEARCH_MAX_METADATA_AGE', $metsis_conf['search_max_metadata_age']);
}
//define('SEARCH_MAX_METADATA_AGE', $metsis_conf['search_max_metadata_age']);
define('BOUNDING_BOX_INITIALLY_COLLAPSED', $metsis_conf['bounding_box_initially_collapsed']);
define('FULL_TEXT_SEARCH_INITIALLY_COLLAPSED', $metsis_conf['full_text_search_initially_collapsed']);

if (isset($metsis_conf['instrument_polarisation_visible'])) {
    define('INSTRUMENT_POLARISATION_VISIBLE', $metsis_conf['instrument_polarisation_visible']);
}
if (isset($metsis_conf['instrument_polarisation_initially_collapsed'])) {
    define('INSTRUMENT_POLARISATION_INITIALLY_COLLAPSED', $metsis_conf['instrument_polarisation_initially_collapsed']);
}
if (isset($metsis_conf['instrument_polarisations'])) {
    //should be removed once faceting of this field works in Solr
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
/*
 * metsis web interface elements}
 */

/**
 * icons{
 */
define('ICONS_PATH', conf_path() . '/files/icons');
/**
 * icons}
 */
/**
 * share these results by mail button
 */
define('SHARE_RESULTS_EMAIL_BUTTON_VISIBLE',$metsis_conf['share_results_email_button_visible']);
define('SHARE_RESULTS_EMAIL_BUTTON_TEXT', $metsis_conf['share_results_email_button_text']);
/**
 * share these results by copy/paste button
 */
define('SHARE_RESULTS_COPY_BUTTON_VISIBLE',$metsis_conf['share_results_copy_button_visible']);
define('SHARE_RESULTS_COPY_BUTTON_TEXT',$metsis_conf['share_results_copy_button_text']);
/**
 * maximum number of rows in the search results table (also used for level 2)
 * 
 */
define('RESULTS_PER_PAGE', $metsis_conf['results_per_page']);

/**
 * definitions from deprecated metsis_search module metsis_search.constants.inc
 */
define('DEBUG', FALSE);
//define('DRUPAL_SITE_NAME', $metsis_conf['drupal_site_name']);
define('CAPDOC_POSTFIX', $metsis_conf['capdoc_postfix']);
define('MISSING_METADATA', $metsis_conf['missing_metadata']);
define('NOT_APPLICABLE_METADATA', $metsis_conf['not_applicable_metadata']);
define('MISSING_OPENDAP', $metsis_conf['missing_opendap']);
define('MISSING_DATA_ACCESS_RESOURCE', $metsis_conf['missing_data_access_resource']);
