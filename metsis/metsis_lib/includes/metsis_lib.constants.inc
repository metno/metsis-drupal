<?php

global $metsis_conf;
define('GLOBALS_CONF_SITE_NAME', $GLOBALS['conf']['site_name']);
if (isset($metsis_conf['drupal_site_name'])) {
  define('METSIS_CONF_DRUPAL_SITE_NAME', $metsis_conf['drupal_site_name']);
}
if (isset($metsis_conf['site_status'])) {
  define('SITE_STATUS', $metsis_conf['site_status']);
} define('METADATA_PREFIX', $metsis_conf['metadata_prefix']);
if (isset($metsis_conf['search_default_metadata_status'])) {
  define('SEARCH_DEFAULT_METADATA_STATUS', $metsis_conf['search_default_metadata_status']);
}
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
if (isset($metsis_conf['metsis_opendap_parser_ip'])) {
  define('METSIS_OPENDAP_PARSER_IP', $metsis_conf['metsis_opendap_parser_ip']);
}
if (isset($metsis_conf['metsis_opendap_parser_port'])) {
  define('METSIS_OPENDAP_PARSER_PORT', $metsis_conf['metsis_opendap_parser_port']);
}
if (isset($metsis_conf['metsis_opendap_parser_service'])) {
  define('METSIS_OPENDAP_PARSER_SERVICE', $metsis_conf['metsis_opendap_parser_service']);
}
if (isset($metsis_conf['sort_by_time'])) {
  define('SORT_BY_TIME', $metsis_conf['sort_by_time']);
}
$metsis_conf['opendap_start_time_strings'] = ['start_time', 'ACQUISITION_START_TIME', 'min_time', 'time_coverage_start'];
$metsis_conf['opendap_stop_time_strings'] = ['stop_time', 'ACQUISITION_STOP_TIME', 'max_time', 'time_coverage_end'];

if (isset($metsis_conf['adc_floating_buttons']) && $metsis_conf['adc_floating_buttons'] === TRUE) {
  define('ADC_FLOATING_BUTTONS', $metsis_conf['adc_floating_buttons']);
}
if (isset($metsis_conf['debug']) && $metsis_conf['debug'] == 1) {
  define('DEBUG', $metsis_conf['debug']);
}
