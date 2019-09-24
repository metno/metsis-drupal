<?php

global $metsis_conf;
if (isset($metsis_conf['site_status'])) {
    define('SITE_STATUS', $metsis_conf['site_status']);
} define('METADATA_PREFIX', $metsis_conf['metadata_prefix']);
if (isset($metsis_conf['search_default_metadata_status'])) {
    define('SEARCH_DEFAULT_METADATA_STATUS', $metsis_conf['search_default_metadata_status']);
} if (isset($metsis_conf['collection'])) {
    define('COLLECTION', adc_get_collection($metsis_conf['collection']));
} if (isset($metsis_conf['solr_core_parent'])) {
    define('SOLR_CORE_PARENT', $metsis_conf['solr_core_parent']);
} if (isset($metsis_conf['solr_core_child'])) {
    define('SOLR_CORE_CHILD', $metsis_conf['solr_core_child']);
} if (isset($metsis_conf['solr_core_map_thumbnails'])) {
    define('SOLR_CORE_MAP_THUMBNAILS', $metsis_conf['solr_core_map_thumbnails']);
} if (isset($metsis_conf['solr_core_config'])) {
    define('SOLR_CORE_CONFIG', $metsis_conf['solr_core_config']);
} if (isset($metsis_conf['metsis_opendap_parser_ip'])) {
    define('METSIS_OPENDAP_PARSER_IP', $metsis_conf['metsis_opendap_parser_ip']);
};
if (isset($metsis_conf['metsis_opendap_parser_port'])) {
    define('METSIS_OPENDAP_PARSER_PORT', $metsis_conf['metsis_opendap_parser_port']);
};
if (isset($metsis_conf['metsis_opendap_parser_service'])) {
    define('METSIS_OPENDAP_PARSER_SERVICE', $metsis_conf['metsis_opendap_parser_service']);
};
