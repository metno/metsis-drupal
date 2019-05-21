<?php

/*
 * *******************************************************************
 *                                                                   *
 * DO NOT EDIT this file unless you are comfortable with             *
 * the inner workings of Drupal and the METSIS modules.              *
 * Mistakes in this file WILL BREAK YOUR SITE.                       *
 *                                                                   *
 * *******************************************************************
 */
$dev_message = '<h1 style="color:red; font-size: 300%;">You are on metsis2.met.no</h1>';
drupal_set_message($dev_message, 'warning');

# METSIS global/shared settings
if (file_exists('sites/all/metsis-global-settings.php')) {
  include 'sites/all/metsis-global-settings.php';
}
/**
 * site-specific settings for metsis multi-sites.
 */
/**
 * This file is loaded after all other multisite global settings have been loaded
 * Place the follwing at the end of the site specific settings.php (after the globa/shared settings if block),
 * replaceing <mutisite site> with the name of the site.
 */
/**
 * 
 * custom <multisite site> settings
 * if (file_exists('sites/<multisite site>.metsis.met.no/metsis.custom.settings/metsis.settings.php')) {
 * include 'sites/<multisite site>.metsis.met.no/metsis.custom.settings/metsis.settings.php';
 * }
 * 
 */
global $metsis_conf;
$metsis_conf['metsis_qsearch_endpoint'] = 'metadata_search';
/**
 * site status: 'dev', 'test' or 'production'{ 
 */
$metsis_conf['site_status'] = 'production';
/**
 * site status}
 */
/**
 * metsis basket server on ARES
 */
$metsis_conf['metsis_basket_server'] = 'basket.metsis.met.no';
$metsis_conf['metsis_basket_server_port'] = '8080';
$metsis_conf['metsis_basket_server_service'] = '/basketService';

/**
 * METSIS OPeNDAP parser service
 */
$metsis_conf['metsis_opendap_parser_ip'] = '157.249.176.100';
$metsis_conf['metsis_opendap_parser_port'] = '8080';
$metsis_conf['metsis_opendap_parser_service'] = '/ometa/ometa';

/**
 * metsis SOLR server on ARES
 */
$metsis_conf['solr_server_ip'] = '157.249.176.182';
$metsis_conf['solr_server_port'] = '8080';

/**
 * SOLR server core details
 */
$metsis_conf['solr_core_parent'] = 'adc-l1';
$metsis_conf['solr_core_child'] = 'adc-l2';
//todo5 $metsis_conf['solr_core_map_thumbnails'] must be changed
//the solr core should be renamed to something more 
//appropriate since it now contains other config data than exclusive to 
//maps
$metsis_conf['solr_core_map_thumbnails'] = 'adc-thumbnail';
$metsis_conf['solr_core_config'] = 'adc-thumbnail';
/**
 * solr metadata prefix
 */
$metsis_conf['metadata_prefix'] = 'mmd_';


$metsis_conf['collections'] = 'ADC';
$metsis_conf['collections_visible'] = TRUE;
$metsis_conf['collections_initially_collapsed'] = TRUE;


/**
 * change these to match your live server details
 */
$metsis_conf['drupal_site_name'] = "sios.metsis.met.no";
$metsis_conf['drupal_site_data_center_desc'] = "SIOS Data Management Service";
/**
 * new as of 2017-05-25{
 * not in use yet
 */
//$metsis_conf['site'] = "xyz.metsis.met.no";
//$metsis_conf['site']['name'] = "xyz";
//$metsis_conf['site']['description'] = "XYZ local VM clone Data Management Service";
//$metsis_conf['site']['user_email_subject'] = "Your data from XYZ data center.";
/**
 * new as of 2017-05-25}
 */
/**
 * capdoc options
 */
$metsis_conf['capdoc_postfix'] = "?SERVICE=WMS&REQUEST=GetCapabilities";

/**
 * missing metadata strings
 */
$metsis_conf['missing_metadata'] = "Missing metadata";
$metsis_conf['not_applicable_metadata'] = "NA";

$metsis_conf['missing_opendap'] = "OPeNDAP access unavailable";
$metsis_conf['missing_data_access_resource'] = "Missing data access resource information. Not added to basket.";

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
  "mmd_activity_type",
  //"mmd_cloud_cover_value",
  //"mmd_collection",
  //"mmd_data_access_description",
  //"mmd_data_access_type",
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
//  "mmd_related_information_type",
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
/**
 * metadata_sort_order}
 */
/**
 * metsis web interface elements{
 */
$metsis_conf['basket_elements_visible'] = TRUE;
//where the basket view is attached
$metsis_conf['basket_endpoint'] = "basket";
$metsis_conf['form_reset_visible'] = TRUE;

/**
 * metsis OL3 WMS{
 */
$longyearbyen = array(
  'lat' => 78.22314167,
  'lon' => 15.64685556,
);
$tromsoe = array(
  'lat' => 69.659,
  'lon' => 18.984,
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
$metsis_conf['wms_map_init_zoom'] = 3.5;
$metsis_conf['wms_restrict_layers'] = 0;
$metsis_conf['wms_visible_layer_title'] = "sea_ice_area_fraction";
//"false" or "true" in the following line is NOT a boolean. It is a simple QUOTED string.
$metsis_conf['wms_product_select'] = "false";
//$metsis_conf['wms_visible_layer_title'] = "averaged sea ice edge";
/**
 * metsis OL3 WMS}
 */
/**
 * default data archive format{
 * "tgz" - tar gnu zip format. This is supported on Linux, UNIX, Windows and Mac
 */
$metsis_conf['default_data_archive_format'] = "tgz";

/**
 * permissions{
 */
$metsis_conf['authentication_required'] = 1;
$metsis_conf['authentication_default_message'] = "The service you requested is only available to authenticated users. Please login or register.";
//$metsis_conf['metsis_basket_authentication_required']['boolean'] = FALSE;
//$metsis_conf['metsis_fimex_authentication_required']['boolean'] = FALSE;
//$metsis_conf['metsis_wms_authentication_required']['boolean'] = FALSE;
/**
 * permissions}
 */
//search form elements

$metsis_conf['limit_empty_search'] = TRUE;
//absolute maximum number of rows to fetch/display
$metsis_conf['search_maximum_rows_to_fetch'] = 3000;
//number of results to display per page
$metsis_conf['results_per_page'] = 17;
//search only for "active" or "Active" datasets
//this paramter is optional. Defaults to "active"
//it's kept here to show its availability
//$metsis_conf['search_default_metadata_status'] = "active";
$metsis_conf['default_start_date'] = " ";
$metsis_conf['default_end_date'] = " ";
// if the user does not refine search look for data younger than
// search_default_metadata_age hours
$metsis_conf['search_max_metadata_age'] = 336.00; // hours
//if none of these is selected to narrow the search then search only for 
//data registered since today less $metsis_conf['search_max_metadata_age']

$metsis_conf['topics_and_variables_visible'] = TRUE;
$metsis_conf['topics_and_variables_initially_collapsed'] = TRUE;
$metsis_conf['institutions_visible'] = TRUE;
$metsis_conf['institutions_initially_collapsed'] = TRUE;
$metsis_conf['investigator_visible'] = TRUE;
$metsis_conf['investigator_initially_collapsed'] = TRUE;
//search results tablulation
$metsis_conf['abstract_visible'] = FALSE;

$metsis_conf['title_visible'] = FALSE;
$metsis_conf['project_visible'] = FALSE;
$metsis_conf['keywords_visible'] = FALSE;

$metsis_conf['platform_long_name_visible'] = FALSE;
$metsis_conf['platform_long_name_initially_collapsed'] = TRUE;
$metsis_conf['results_cloud_cover_value_visible'] = FALSE;
$metsis_conf['cloud_cover_value_visible'] = TRUE;
$metsis_conf['cloud_cover_value_initially_collapsed'] = TRUE;
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
$metsis_conf['data_collection_period_initially_collapsed'] = TRUE;
$metsis_conf['bounding_box_initially_collapsed'] = TRUE;
$metsis_conf['full_text_search_initially_collapsed'] = TRUE;

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
/*
 * metsis_search search and result tabulation elements}
 */

/*
 * fimex{
 * where the transformation (fimex) interface is attached
 */
//
$metsis_conf['transformation_server_getcapabilities'] = "http://157.249.177.189/cgi-bin/pywps.cgi?service=wps&version=1.0.0&request=getCapabilities";
$metsis_conf['transformation_endpoint'] = "metsis_fimex";
$metsis_conf['transformation_output_format_visible'] = TRUE;
$metsis_conf['transformation_exclude_variables'] = 'projection,Granules_Level_1C_Tile_ID_metadata,Level_1C_DataStrip_ID_metadata,Level_1C_User_Product_metadata,swathList';

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
$metsis_conf['message']['visible'] = FALSE;
$metsis_conf['message']['under_construction'] = "This site is under construction. Communication with backends may be slow.";
/**
 * messages to users}
 */
/**
 * OPeNDAP{
 * Deprecated. Do not change. This is a temporary variable soon to be removed.
 */
// parsing opendap is VERY expensive and brings response time right up
// especially with THREDDS not responding
// this is now deprecated and must be remvoved from the code
// once the OPeNDAP parser service is running AND
// featureTypes are indexed in SOLR
$metsis_conf['inspect_opendap_streams'] = 0;
/**
 * OPeNDAP}
 */
/**
 * button texts for search and results{
 */
$metsis_conf['dar_http_button_text'] = 'Download data';
//$metsis_conf['dar_odata_button_text'] = 'Download .SAFE product';
$metsis_conf['dar_opendap_button_text'] = 'OPeNDAP';
$metsis_conf['dar_ogc_wms_button_text'] = 'OGC WMS';
$metsis_conf['solr_metadata_button_text'] = 'Metadata';
/**
 * button texts for search and results}
 */
/*
 * timeseries{
 */
//recommend you create a page and limit the block to appear only 
//on that page
$metsis_conf['ts_server'] = "157.249.176.137";
$metsis_conf['ts_endpoint'] = "ts";
//button label for the time series visualizaion button in the search results table
$metsis_conf['ts_visualization_button_text'] = "Visualize";
// all site admin configuration of metsis_timeseries module
//default maximum number of point to plot 
//if the variable has more than ts_plot_npoints of data points
//it will be resampled to give ts_plot_npoints to cover all values 
$metsis_conf['ts_plot_npoints'] = 150;
//These variable should not appear in time series plotting options
$metsis_conf['ts_exclude_variables'] = array('latitude', 'longitude', 'height');
/*
 * timeseries}
 */
/**
 * CSV{
 */
//recommend you create a page and limit the block to appear only 
//on that page
$metsis_conf['csv_server'] = "157.249.176.137";
$metsis_conf['csv_endpoint'] = "csv";
//button label for the CSV data download button in the search results table
$metsis_conf['csv_button_text'] = "ASCII";
//resample to every csv_npoints
$metsis_conf['csv_npoints'] = 1;
// all site admin configuration of metsis_csv module
//These variable should not appear in CSV options
$metsis_conf['csv_exclude_variables'] = array('latitude', 'longitude', 'height');

/**
 * CSV}
 */
/**
 * elements{
 * 
 */
$metsis_conf['elements_endpoint'] = "child";
$metsis_conf['elements_button_text'] = "Child data";
$metsis_conf['required_child_metadata'] = 'mmd_personnel_organisation,'
  . 'mmd_metadata_identifier,'
  . 'mmd_title,'
  . 'mmd_temporal_extent_start_date,'
  . 'mmd_temporal_extent_end_date,'
  . 'mmd_data_access_resource,'
  . 'mmd_related_information_resource,'
  . 'mmd_personnel_name';
/**
 * elements}
 */
/**
 * share these results by mail
 */
$metsis_conf['share_results_email_button_visible'] = TRUE;
$metsis_conf['share_results_email_button_text'] = "Share results";
/**
 * 
 */
/**
 * share these results by copy/paste button
 */
$metsis_conf['share_results_copy_button_visible'] = FALSE;
$metsis_conf['share_results_copy_button_text'] = "Search ID";
/**
 * 
 */
/**
 * level two or child datasets{
 */
//$metsis_conf['leveltwo_endpoint'] = "lt";
//$metsis_conf['leveltwo_button_text'] = "Level 2";
/**
 * level two or child datasets}
 */
/**
 * search resutls sort orders{
 * 
 */
$metsis_conf['sort_by_time'] = 'desc';
//$metsis_conf['sort_by_time'] = 'asc';
/**
 * search resutls sort order}
 */
