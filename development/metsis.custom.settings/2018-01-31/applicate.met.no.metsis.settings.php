<?php

/**
 * @file
 * site-specific settings for metsis multi-sites.
 * The general rule is create variable in a such way that 
 * if they are undefined the site will still work.
 * The alternative is to ALWAYS test for definition before use.
 * 
 */
/**
 * This file is loaded after all other multisite global settings have been loaded
 * Place the follwing at the end of the site specific settings.php (after the global/shared settings if block),
 * replaceing <mutisite site> with the name of the site.
 */
/**
 * 
  # custom <multisite site> settings
  if (file_exists('sites/<multisite site>.metsis.met.no/metsis.custom.settings/metsis.settings.php')) {
  include 'sites/<multisite site>.metsis.met.no/metsis.custom.settings/metsis.settings.php';
  }
 * 
 */
global $metsis_conf;
/**
 * a few of these are candidates for global settings files, but are kept here to ensure maximum flexibility.
 */
/*
 * metsis basket server on ARES
 */
//metsis_basket_server has an entry in the global DNS, but is (and should be) accessible
//only to the metsis machines on ARES
//$metsis_conf['metsis_basket_server_ip']='157.249.176.100';

$metsis_conf['metsis_basket_server'] = 'basket.metsis.met.no';
$metsis_conf['metsis_basket_server_port'] = '8080';
$metsis_conf['metsis_basket_server_service'] = '/basketService';


//$metsis_conf['solr_server_ip']   = '10.99.3.36';
//on Ares
$metsis_conf['solr_server_ip'] = '157.249.176.182';
$metsis_conf['solr_server_port'] = '8080';

/**
 * SOLR server core details
 */
$metsis_conf['solr_core_parent'] = 'applicate-l1';
$metsis_conf['solr_core_child'] = 'applicate-l2';
$metsis_conf['solr_core_map_thumbnails'] = 'applicate-thumbnail';
/**
 * visualization server details{
 */
$metsis_conf['visualization_server'] = 'https://visualization.metsis.met.no/ts/';
/**
 * visualization server details}
 */
/**
 * local dev vm server
 * change these to match your live server details
 */
$metsis_conf['drupal_server'] = 'applicate.met.no';
$metsis_conf['drupal_server_protocol'] = "https";
$metsis_conf['drupal_server_port'] = '80';
$metsis_conf['drupal_server_ssl_port'] = '443';
$metsis_conf['drupal_site_name'] = "applicate.met.no";
$metsis_conf['drupal_site_data_center_desc'] = "Applicate Data Management Service";



/**
 * background maps from public servers
 */
$metsis_conf['metno_public_wms'] = "public-wms.met.no";
$metsis_conf['mapthumb_base_image'] = "http://"
  . $metsis_conf['metno_public_wms'] .
  "/backgroundmaps/northpole.map"
  . "?SERVICE=WMS"
  . "&REQUEST=GetMap"
  . "&VERSION=1.1.1"
  . "&FORMAT=image%2Fpng&SRS=EPSG:32661"
  . "&BBOX=-3000000,-3000000,7000000,7000000"
  . "&WIDTH=64"
  . "&HEIGHT=64"
  . "&EXCEPTIONS=application%2Fvnd.ogc.se_inimage"
  . "&TRANSPARENT=true"
  . "&LAYERS=borders"
  . "&STYLES=";


/**
 * solr metadata prefix
 * All metadata with the exception of id are prefixed with "mmd_" in SOLR
 * "mmd_" is peculiar to METNO and should therefore be parametrized here
 * and provided as a configuration parameter in the configuration interface.
 */
$metsis_conf['metadata_prefix'] = 'mmd_';

/**
 * capdoc options
 */
//$metsis_conf['capdoc_postfix'] = "?SERVICE=WMS&version=1.3.0&request=GetCapabilities";
$metsis_conf['capdoc_postfix'] = "?SERVICE=WMS&REQUEST=GetCapabilities";

/**
 * missing metadata strings
 */
$metsis_conf['missing_metadata'] = "Missing metadata";
$metsis_conf['not_applicable_metadata'] = "NA";

$metsis_conf['missing_opendap'] = "OPeNDAP access unavailable";
$metsis_conf['missing_data_access_resource'] = "Missing data access resource information. Not added to basket.";
/**
 * HTTP(S)
 */
//$conf['https'] = FALSE;
/*
 * metadata_sort_order{
 * See comment at https://phab.met.no/T2849
 * "Preferred order should bes something like title, abstract, PI (from personnel), start/end, boundingbox, 
 * access URLs and then the rest."
 * this array reflects metadata as they are in SOLR 
 * a few elements (e.g. "bbox" and "id") are indexed in SOLR without the prefix METADATA_PREFIX
 * the reason for this mixed convention is not clear as of this writing (2016-10-18)
 * c.f. MMD specification document
 * 
 */
$metsis_conf['metadata_visible'] = array(
  "mmd_title",
  "mmd_abstract",
  "mmd_personnel_name",
  "mmd_personnel_role",
  "mmd_personnel_organisation",
  "mmd_temporal_extent_start_date",
  "mmd_temporal_extent_end_date",
  "mmd_data_access_resource",
  "bbox",
  //"_version_",
  "mmd_metadata_identifier",
  //"mmd_activity_type",
  //"mmd_cloud_cover_value",
  //"mmd_collection",
  //"mmd_data_access_description",
  "mmd_data_access_type",
  "mmd_data_access_wms_layers_wms_layer",
  "mmd_data_center_contact_email",
  "mmd_data_center_contact_name",
  "mmd_data_center_contact_role",
  "mmd_data_center_data_center_name_long_name",
  "mmd_data_center_data_center_name_short_name",
  "mmd_data_center_data_center_url",
  "mmd_dataset_language",
  "mmd_dataset_production_status",
  "mmd_instrument_long_name",
  "mmd_instrument_short_name",
  "mmd_iso_topic_category",
  "mmd_keywords_keyword",
  "mmd_last_metadata_update",
  "mmd_metadata_status",
  "mmd_operational_status",
  "mmd_platform_long_name",
  "mmd_platform_short_name",
  "mmd_project_long_name",
  "mmd_project_short_name",
  "mmd_related_information_resource",
  "mmd_related_information_type",
);
$metsis_conf['metadata_sort_order'] = array(
  "mmd_title",
  "mmd_abstract",
  "mmd_personnel_name",
  "mmd_personnel_role",
  "mmd_personnel_organisation",
  "mmd_temporal_extent_start_date",
  "mmd_temporal_extent_end_date",
  "mmd_data_access_resource",
  "bbox",
);
/*
 * metadata_sort_order}
 */
/*
 * metsis web interface elements{
 */
//These will be moved to admin interface as options
$metsis_conf['basket_elements_visible'] = TRUE;
//where the basket view is attached
$metsis_conf['basket_endpoint'] = "basket";
$metsis_conf['form_reset_visible'] = FALSE;
/*
 * metsis web interface elements}
 */
/*
 * metsis OL3 WMS{
 */
$longyearbyen = array(
  'lat' => 78.22314167,
  'lon' => 15.64685556,
);
$metno = array(
  'lat' => 59.94266,
  'lon' => 10.72051,
);
$metsis_conf['search_map_center_lat'] = $longyearbyen['lat'];
$metsis_conf['search_map_center_lon'] = $longyearbyen['lon'];
$metsis_conf['search_map_init_zoom'] = 4.0;
$metsis_conf['wms_map_center_lat'] = $longyearbyen['lat'];
$metsis_conf['wms_map_center_lon'] = $longyearbyen['lon'];
$metsis_conf['wms_map_init_zoom'] = 5.0;
$metsis_conf['wms_restrict_layers'] = 0;
//$metsis_conf['wms_visible_layer_title'] = "Reflectance in band B8";
$metsis_conf['wms_visible_layer_title'] = "sea_ice_area_fraction";
//"false" or "true" in the following line is NOT a boolean. It is a simple QUOTED string.
$metsis_conf['wms_product_select'] = "false";
/*
 * metsis OL3 WMS}
 */
/*
 * default data archive format{
 * "tgz" - tar gnu zip format. This is supported on Linux, UNIX, Windows and Mac
 */
$metsis_conf['default_data_archive_format'] = "tgz";
/*
 * default data archive format}
 */


/*
 * metsis_search search and result tabulation elements{
 */
$metsis_conf['limit_empty_search'] = TRUE;
//absolute maximum number of rows to fetch/display
//maximum number of rows to fetch/display form SOLR if 
//user supplies no search criterion
$metsis_conf['empty_search_maximum_rows_to_fetch'] = 100;
//absolute maximum number of rows to fetch/display
$metsis_conf['search_maximum_rows_to_fetch'] = 3000;
// show only active datasets
$metsis_conf['search_default_metadata_status'] = "active";
//default_start_date ("Start date") will be set to 
//today - search_max_metadata_age 
//if it is set to "" (empty string) here
$metsis_conf['default_start_date'] = "1967-01-01";
//default_end_date ("End date") will be set to exactly the string defined here. 
$metsis_conf['default_end_date'] = "";
// if the user does not refine search look for data younger than
// search_default_metadata_age hours
$metsis_conf['search_max_metadata_age'] = 336.00; // hours
//search form elements
$metsis_conf['topics_and_variables_visible'] = TRUE;
$metsis_conf['institutions_visible'] = TRUE;
$metsis_conf['investigator_visible'] = TRUE;
//search results tablulation
$metsis_conf['abstract_visible'] = TRUE;

$metsis_conf['title_visible'] = FALSE;
$metsis_conf['project_visible'] = TRUE;
$metsis_conf['keywords_visible'] = TRUE;

$metsis_conf['platform_long_name_visible'] = FALSE;
$metsis_conf['results_cloud_cover_value_visible'] = FALSE;
$metsis_conf['cloud_cover_value_visible'] = FALSE;
$metsis_conf['cloud_cover_value_search_options'] = array(
  "<10%",
  "<20%",
  "<30%",
  "<40%",
  "<50%",
  "<60%",
  "<70%",
  "<80%",
  "<90%",
  ">90%",
);
//do not set the following elements to FALSE - it makes no sense!
$metsis_conf['datasetName_visible'] = TRUE;
$metsis_conf['collection_period_visible'] = TRUE;
/*
 * most formats are possible
 * Examples:
 *  
 *   'Y-m-d\TH:i:sP'    => e.g. 2005-08-15T15:52:01+00:00 (compatible with ISO-8601)
 *   'D, d M y H:i:s O' => e.g. Mon, 15 Aug 05 15:52:01 +0000
 *   'l, d-M-y H:i:s T' => e.g. Monday, 15-Aug-05 15:52:01 UTC
 *   'D, d M y H:i:s O' => e.g. Mon, 15 Aug 05 15:52:01 +0000
 *   'D, d M Y H:i:s O' => e.g. Mon, 15 Aug 2005 15:52:01 +0000
 *   'D, d M Y H:i:s O' => e.g. Mon, 15 Aug 2005 15:52:01 +0000
 *   'D, d M Y H:i:s O' => e.g. Mon, 15 Aug 2005 15:52:01 +0000
 *   'Y-m-d\TH:i:sP'    => e.g. 2005-08-15T15:52:01+00:00
 *   see http://php.net/manual/en/class.datetime.php for more details
 */
$metsis_conf['results_date_display_format'] = ''; // set "''" for SOLR index format
//set the number of decimal of decimal places for all floating point numbers displayed
$metsis_conf['results_number_decimal_display_format'] = 2;
// permissions and messages to users
// pages are left open by default for the moment (testing)
// *_AUTHENTICATION_REQUIRED must be set in the *.constants.inc for the module
$metsis_conf['authentication_required'] = TRUE;
$metsis_conf['authentication_required_message'] = "This page is is only available to users that are logged in. Please login or register.";

/*
 * metsis_search search and result tabulation elements}
 */
/*
 * fimex{
 * where the transformation (fimex) interface is attached
 */
//
$metsis_conf['transformation_endpoint'] = "metsis_fimex";
/*
 * fimex}
 */
/*
 * OL3 WMS{
 */
$metsis_conf['wms_endpoint'] = "metsis/map/wms";
/*
 * 
 */
/*
 * permissions{
 * these will be deprecated and replaced by Drupal roles etc.
 */
$metsis_conf['authentication_default_message'] = "This page is only available to users that are logged in. Please login or register.";

$metsis_conf['metsis_search_authentication_required']['boolean'] = TRUE;
//$metsis_conf['metsis_search_authentication_required']['message'] = "This page is currently only available to users by invitation. Please login or visit us later.";

$metsis_conf['metsis_basket_authentication_required']['boolean'] = TRUE;
//$metsis_conf['metsis_basket_authentication_required']['message'] = "Basket functionality is only available to users that are logged in. Please login or register.";

$metsis_conf['metsis_fimex_authentication_required']['boolean'] = TRUE;
//$metsis_conf['metsis_fimex_authentication_required']['message'] = "Data transformation is only available to users that are logged in. Please login or register.";
//only multiple visualization is limited to authenticated users
$metsis_conf['metsis_wms_authentication_required']['boolean'] = FALSE;
//$metsis_conf['metsis_wms_authentication_required']['message'] = "Multiple product visualization is only available to users that are logged in. Please login or register.";
/*
 * permissions}
 */


/**
 * metsis search configuration{
 */
$metsis_conf['search_form_fields_empty_check'] = array(
  'chosen_topics_and_variables_a' => FALSE,
  'finished_after' => TRUE,
  'finished_before' => TRUE,
  'bbox_top_left_lon' => TRUE,
  'bbox_top_left_lat' => TRUE,
  'bbox_bottom_right_lon' => TRUE,
  'bbox_bottom_right_lat' => TRUE,
  'chosen_full_text_search' => TRUE,
  'chosen_investigator' => TRUE,
  'cloud_cover_value' => array(
    'chosen_cloud_cover_value' => TRUE,
  )
);
/**
 * metsis search configuration}
 */
/**
 * messages to users{
 */
$metsis_conf['message'] = [];
$metsis_conf['message']['visible'] = TRUE;
$metsis_conf['message']['under_construction'] = "This site is under construction. Communication with backends may be slow.";

/**
 * messages to users}
 */
/**
 * OPeNDAP{
 */
// parsing opendap is VERY expensive and brings response time right up
// especially with THREDDS not responding

$metsis_conf['inspect_opendap_streams'] = 0;
/**
 * OPeNDAP}
 */
/**
 * button texts for search and results{
 */
$metsis_conf['dar_http_button_text'] = 'Download data';
$metsis_conf['dar_odata_button_text'] = 'Download .SAFE product';
//OPeNDAP and OGC WMS are currently not in use (not implemented)
//$metsis_conf['dar_opendap_button_text'] = 'OPeNDAP';
//$metsis_conf['dar_ogc_wms_button_text'] = 'OGC WMS';
$metsis_conf['solr_metadata_button_text'] = 'Metadata';
/**
 * button texts for search and results}
 */