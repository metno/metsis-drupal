<?php

/**
 * DEPRECATED. DO NOT USE
 */
















//global $metsis_conf;

/**
 * DEBUG
 * Set this to true for debugging
 */
//define('DEBUG', TRUE);
//define('DEBUG', FALSE);

/*
 * These must be configurable from the admin interface
 */
/**
 * servers setup
 */
/**
 * solr server on dev openstack
 */
//define('SOLR_SERVER_IP', '10.99.3.36');
//define('SOLR_SERVER_IP', $metsis_conf['solr_server_ip']);
//define('SOLR_SERVER_PORT', '8080');
//define('SOLR_SERVER_PORT', $metsis_conf['solr_server_port']);
//define('SOLR_SERVER_SSL_PORT', $metsis_conf['solr_server_ssl_port']);
/**
 * local dev vm server
 * change these to match your live server details
 */
//define('DRUPAL_SERVER', 'localhost');
//define('DRUPAL_SERVER_PORT', '33380');
//define('DRUPAL_SITE_NAME', 'cms01');
//define('DRUPAL_SERVER', $metsis_conf['drupal_server']);
//define('DRUPAL_SERVER_PROTOCOL', $metsis_conf['drupal_server_protocol']);
//define('DRUPAL_SERVER_PORT', $metsis_conf['drupal_server_port']);
//define('DRUPAL_SERVER_SSL_PORT', $metsis_conf['drupal_server_ssl_port']);
//define('DRUPAL_SITE_NAME', $metsis_conf['drupal_site_name']);

/**
 * server settings for cms01-normap-dev.met.no
 */
//define('DRUPAL_SERVER', 'cms01-normap-dev.met.no');
//define('DRUPAL_SERVER_PORT', '80');
//define('DRUPAL_SITE_NAME', '');



/**
 * SOLR server core details
 */
//define('SOLR_CORE_PARENT', 'l1');
//define('SOLR_CORE_CHILD', 'l2');
//define('SOLR_CORE_MAP_THUMBNAILS', 'thumbnail');
//
//refactored to metsis_lib.constants.inc
//
//define('SOLR_CORE_PARENT', $metsis_conf['solr_core_parent']);
//define('SOLR_CORE_CHILD', $metsis_conf['solr_core_child']);
//define('SOLR_CORE_MAP_THUMBNAILS', $metsis_conf['solr_core_map_thumbnails']);
//apache solr extension test
//define('SOLR_SERVER_HOSTNAME', '10.99.3.36');
//define('SOLR_SERVER_PATH', '/solr/l1');
/**
 * end servers setup
 */
/**
 * background maps from public servers
 */
//define('METNO_PUBLIC_WMS', "public-wms.met.no");
//define('MAPTHUMB_BASE_IMAGE', "http://"
//    . METNO_PUBLIC_WMS .
//    "/backgroundmaps/northpole.map"
//    . "?SERVICE=WMS"
//    . "&REQUEST=GetMap"
//    . "&VERSION=1.1.1"
//    . "&FORMAT=image%2Fpng&SRS=EPSG:32661"
//    . "&BBOX=-3000000,-3000000,7000000,7000000"
//    . "&WIDTH=64"
//    . "&HEIGHT=64"
//    . "&EXCEPTIONS=application%2Fvnd.ogc.se_inimage"
//    . "&TRANSPARENT=true"
//    . "&LAYERS=borders"
//    . "&STYLES="
//);
//define('METNO_PUBLIC_WMS', "public-wms.met.no");
//define('MAPTHUMB_BASE_IMAGE', $metsis_conf['mapthumb_base_image']);
/**
 * background maps from public servers
 */
/**
 * solr metadata prefix
 * All metadata with the exception of id are prefixed with "mmd_" in SOLR
 * "mmd_" is peculiar to METNO and should therefore be parametrized here
 * and provided as a configuration parameter in the configuration interface.
 */
//define('METADATA_PREFIX', 'mmd_');
//define('METADATA_PREFIX', $metsis_conf['metadata_prefix']);

/**
 * capdoc options
 */
//define('CAPDOC_POSTFIX', "?SERVICE=WMS&version=1.3.0&request=GetCapabilities");
//define('CAPDOC_POSTFIX', $metsis_conf['capdoc_postfix']);

/**
 * missing metadata strings
 */
//define('MISSING_METADATA', "Missing metadata");
//define('MISSING_OPENDAP', "OPeNDAP access unavailable");
//define('MISSING_METADATA', $metsis_conf['missing_metadata']);
//define('NOT_APPLICABLE_METADATA', $metsis_conf['not_applicable_metadata']);
//define('MISSING_OPENDAP', $metsis_conf['missing_opendap']);
//define('MISSING_DATA_ACCESS_RESOURCE', $metsis_conf['missing_data_access_resource']);

/*
 * metsis web interface elements{
 */
//These will be moved to admin interface as options
//define('BASKET_ELEMENTS_VISIBLE', $metsis_conf['basket_elements_visible']);
//define('FORM_RESET_VISIBLE', $metsis_conf['form_reset_visible']);
//define('TOPICS_AND_VARIABLES_VISIBLE', $metsis_conf['topics_and_variables_visible']);
//define('INSTITUTIONS_VISIBLE', $metsis_conf['institutions_visible']);
//define('INVESTIGATOR_VISIBLE', $metsis_conf['investigator_visible']);
//define('ABSTRACT_VISIBLE', $metsis_conf['abstract_visible']);
//define('DATASETNAME_VISIBLE', $metsis_conf['datasetName_visible']);
//define('TITLE_VISIBLE', $metsis_conf['title_visible']);
//define('PROJECT_VISIBLE', $metsis_conf['project_visible']);
//define('KEYWORDS_VISIBLE', $metsis_conf['keywords_visible']);
//define('COLLECTION_PERIOD_VISIBLE', $metsis_conf['collection_period_visible']);
//define('PLATFORM_LONG_NAME_VISIBLE', $metsis_conf['platform_long_name_visible']);
//define('CLOUD_COVER_VALUE_VISIBLE', $metsis_conf['cloud_cover_value_visible']);
//define('RESULTS_DATE_DISPLAY_FORMAT', $metsis_conf['results_date_display_format']);
//absolute maximum number of rows to fetch/display
//define('SEARCH_MAXIMUM_ROWS_TO_FETCH', $metsis_conf['search_maximum_rows_to_fetch']);
//if (isset($metsis_conf['search_max_metadata_age'])) {
//    define('SEARCH_MAX_METADATA_AGE', $metsis_conf['search_max_metadata_age']);
//}
//define('SEARCH_MAX_METADATA_AGE', $metsis_conf['search_max_metadata_age']);

/*
 * metsis web interface elements}
 */

