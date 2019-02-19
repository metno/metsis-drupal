<?php

/**
 * TODO3
 * ALL constants must be defined here. The use of $metsis_conf[...] is 
 * deprecated and all instances must be refactored to here.
 */
global $metsis_conf;

define('GLOBALS_CONF_SITE_NAME', $GLOBALS['conf']['site_name']);
if (isset($metsis_conf['drupal_site_name'])) {
    define('METSIS_CONF_DRUPAL_SITE_NAME', $metsis_conf['drupal_site_name']);
}
if (isset($metsis_conf['site_status'])) {
    define('SITE_STATUS', $metsis_conf['site_status']);
}


define('METADATA_PREFIX', $metsis_conf['metadata_prefix']);

if (isset($metsis_conf['search_default_metadata_status'])) {
    define('SEARCH_DEFAULT_METADATA_STATUS', $metsis_conf['search_default_metadata_status']);
}

// remove this collections behavior. 
//if (isset($metsis_conf['collections'])) {
//    define('COLLECTIONS', adc_get_collections($metsis_conf['collections']));
//    define('COLLECTIONS_SET_GLOBALLY',1);
//}
if (isset($metsis_conf['solr_core_parent'])) {
    define('SOLR_CORE_PARENT', $metsis_conf['solr_core_parent']);
}
if (isset($metsis_conf['solr_core_child'])) {
    define('SOLR_CORE_CHILD', $metsis_conf['solr_core_child']);
}
if (isset($metsis_conf['solr_core_map_thumbnails'])) {
    define('SOLR_CORE_MAP_THUMBNAILS', $metsis_conf['solr_core_map_thumbnails']);
}

if (isset($metsis_conf['solr_core_config'])) {
    define('SOLR_CORE_CONFIG', $metsis_conf['solr_core_config']);
}

/**
 * METSIS OPeNDAP parser service{
 */
if (isset($metsis_conf['metsis_opendap_parser_ip'])) {
    define('METSIS_OPENDAP_PARSER_IP', $metsis_conf['metsis_opendap_parser_ip']);
}
if (isset($metsis_conf['metsis_opendap_parser_port'])) {
    define('METSIS_OPENDAP_PARSER_PORT', $metsis_conf['metsis_opendap_parser_port']);
}
if (isset($metsis_conf['metsis_opendap_parser_service'])) {
    define('METSIS_OPENDAP_PARSER_SERVICE', $metsis_conf['metsis_opendap_parser_service']);
}
/**
 * METSIS OPeNDAP parser service}
 */
if (isset($metsis_conf['sort_by_time'])) {
    define('SORT_BY_TIME', $metsis_conf['sort_by_time']);
}

/**
 * OPeNDAP variations{
 */
/**
 * extract start and stop times{
 * Need a more elegant solution to this
 * OPeNDAP naming for these variable varies from project to project 
 * a list has to be kept of all the possible variations !!
 * alternative (expensive) is to calculated these values based on the actual data
 */
$metsis_conf['opendap_start_time_strings'] = ['start_time', 'ACQUISITION_START_TIME', 'min_time', 'time_coverage_start'];
$metsis_conf['opendap_stop_time_strings'] = ['stop_time', 'ACQUISITION_STOP_TIME', 'max_time', 'time_coverage_end'];
/**
 * OPeNDAP variations}
 */  