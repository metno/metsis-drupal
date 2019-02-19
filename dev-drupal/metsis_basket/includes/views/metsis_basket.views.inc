<?php

/**
 * Implements hook_views_data().
 * metsis_basket_views_data() {
 */
function metsis_basket_views_data() {
    $data = array();
    $data['metsis_basket']['table']['group'] = t('METSIS Basket');
    $data['metsis_basket']['table']['base'] = array(
      'title' => t('METSIS Basket'),
      'help' => t('Contains METSIS basket records and fields that are to be available in Views.'),
    );
    $data['metsis_basket']['table']['join'] = array(
      'node' => array(
        'left_field' => 'nid',
        'field' => 'node_id',
      ),
    );
    /*
     * fields{
     */
    //iid
    // The IID field - this is the Item ID in the basket
    $data['metsis_basket']['iid'] = array(
      'title' => t('IID'),
      'help' => t('The Item ID.'),
      'field' => array(
        'handler' => 'views_handler_field_numeric',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_numeric',
      ),
    );
    //uid
    // The User ID field
    $data['metsis_basket']['uid'] = array(
      'title' => t('UID'),
      'help' => t('The user ID.'),
      'field' => array(
        'handler' => 'views_handler_field_numeric',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_numeric',
      ),
      // contextual filter argument
      'argument' => array(
        'handler' => 'views_handler_argument_node_nid',
        'numeric' => TRUE,
        'validate type' => 'nid',
      ),
    );
    //user_name
    // The user name field
    $data['metsis_basket']['user_name'] = array(
      'title' => t('User name'),
      'help' => t('The user name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //session_id
    // The session_id
    $data['metsis_basket']['session_id'] = array(
      'title' => t('Session ID'),
      'help' => t('The session ID'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //basket_timestamp
    // basket_timestamp{
    $data['metsis_basket']['basket_timestamp'] = array(
      'title' => t('Basket timestamp'),
      'help' => t('The timestamp for when item was added to basket'),
      'field' => array(
        'handler' => 'views_handler_field_date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort_date',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_date',
      ),
    );
    // basket_timestamp}
    //solr_id
    $data['metsis_basket']['solr_id'] = array(
      'title' => t('SOLR id'),
      'help' => t('SOLR id field'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //_version_
    $data['metsis_basket']['_version_'] = array(
      'title' => t('SOLR _version_'),
      'help' => t('SOLR _version_'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //last_metadata_update
    $data['metsis_basket']['last_metadata_update'] = array(
      'title' => t('Last metadata update'),
      'help' => t('last metadata update'),
      'field' => array(
        'handler' => 'views_handler_field_date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort_date',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_date',
      ),
    );
    //personell_email
    // The  personell_email field
    $data['metsis_basket']['personell_email'] = array(
      'title' => t('Personell email'),
      'help' => t('The personell_email'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //platform_long_name
    // The platform_long_name field
    $data['metsis_basket']['platform_long_name'] = array(
      'title' => t('Platform long name'),
      'help' => t('platform_long_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_center_contact_name
    // The data_center_contact_name field
    $data['metsis_basket']['data_center_contact_name'] = array(
      'title' => t('Data center contact name'),
      'help' => t('data_center_contact_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //collection
    // The collection field
    $data['metsis_basket']['collection'] = array(
      'title' => t('Collection'),
      'help' => t('collection'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //geographic_extent_rectangle_east
    // The geographic_extent_rectangle_east field
    $data['metsis_basket']['geographic_extent_rectangle_east'] = array(
      'title' => t('Geographic extent rectangle east'),
      'help' => t('geographic_extent_rectangle_east'),
      'field' => array(
        'handler' => 'views_handler_field',
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
        'click sortable' => TRUE,
      ),
    );
    //geographic_extent_rectangle_west
    // The geographic_extent_rectangle_west field
    $data['metsis_basket']['geographic_extent_rectangle_west'] = array(
      'title' => t('Geographic extent rectangle west'),
      'help' => t('geographic_extent_rectangle_west'),
      'field' => array(
        'handler' => 'views_handler_field',
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //geographic_extent_rectangle_north
    // The geographic_extent_rectangle_north field
    $data['metsis_basket']['geographic_extent_rectangle_north'] = array(
      'title' => t('Geographic extent rectangle north'),
      'help' => t('geographic_extent_rectangle_north'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //geographic_extent_rectangle_south
    // The geographic_extent_rectangle_south field
    $data['metsis_basket']['geographic_extent_rectangle_south'] = array(
      'title' => t('Geographic extent rectangle south'),
      'help' => t('geographic_extent_rectangle_south'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_center_data_center_url
    // The data_center_data_center_url field
    $data['metsis_basket']['data_center_data_center_url'] = array(
      'title' => t('Data center data center URL'),
      'help' => t('data_center_data_center_url'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //platform_short_name
    // The platform_short_name field
    $data['metsis_basket']['platform_short_name'] = array(
      'title' => t('Platform short name'),
      'help' => t('platform_short_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //related_information_resource
    // The related_information_resource field
    $data['metsis_basket']['related_information_resource'] = array(
      'title' => t('Related information resource'),
      'help' => t('related_information_resource'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //project_long_name
    // The project_long_name field
    $data['metsis_basket']['project_long_name'] = array(
      'title' => t('Project long name'),
      'help' => t('project_long_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_access_resource_HTTP
    // The  data_access_resource_http field
    $data['metsis_basket']['data_access_resource_http'] = array(
      'title' => t('Data access resource HTTP'),
      'help' => t('data_access_resource_http'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_access_resource_OPeNDAP
    // The  data_access_resource_opendap field
    $data['metsis_basket']['data_access_resource_opendap'] = array(
      'title' => t('Data access resource OPenDAP'),
      'help' => t('data_access_resource_opendap'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_access_resource_OGC_WMS
    // The data_access_resource_ogc_wms field
    $data['metsis_basket']['data_access_resource_ogc_wms'] = array(
      'title' => t('Data access resource OGC WMS'),
      'help' => t('data_access_resource_ogc_wms'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
//    //data_access_resource_HTTPserver
//    // The  data_access_resource_httpserver field
//    $data['metsis_basket']['data_access_resource_httpserver'] = array(
//      'title' => t('Data access resource HTTPserver'),
//      'help' => t('data_access_resource_httpserver'),
//      'field' => array(
//        'handler' => 'views_handler_field',
//        'click sortable' => TRUE,
//      ),
//      'sort' => array(
//        'handler' => 'views_handler_sort',
//      ),
//      'filter' => array(
//        'handler' => 'views_handler_filter_string',
//      ),
//    );
    //data_access_resource_ODATA
    // The  data_access_resource_ODATA field
    $data['metsis_basket']['data_access_resource_ODATA'] = array(
      'title' => t('Data access resource ODATA'),
      'help' => t('data_access_resource_ODATA'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //dataset_production_status
    // The dataset_production_status field
    $data['metsis_basket']['dataset_production_status'] = array(
      'title' => t('Dataset production status'),
      'help' => t('dataset_production_status'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //access_constraint
    // The access_constraint field
    $data['metsis_basket']['access_constraint'] = array(
      'title' => t('Access constraint'),
      'help' => t('access_constraint'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //iso_topic_category
    // The iso_topic_category field
    $data['metsis_basket']['iso_topic_category'] = array(
      'title' => t('ISO topic category'),
      'help' => t('iso_topic_category'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //temporal_extent_start_date
    // The temporal_extent_start_date field
    $data['metsis_basket']['temporal_extent_start_date'] = array(
      'title' => t('Temporal extent start date'),
      'help' => t('temporal_extent_start_date'),
      'field' => array(
        'handler' => 'views_handler_field_date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort_date',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_date',
      ),
    );
    //temporal_extent_end_date
    // The temporal_extent_end_date field
    $data['metsis_basket']['Temporal extent end date'] = array(
      'title' => t('temporal_extent_end_date'),
      'help' => t('temporal_extent_end_date'),
      'field' => array(
        'handler' => 'views_handler_field_date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort_date',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_date',
      ),
    );
    //data_center_data_center_name_long_name
    // The data_center_data_center_name_long_name field
    $data['metsis_basket']['data_center_data_center_name_long_name'] = array(
      'title' => t('Data center data center name long name'),
      'help' => t('data_center_data_center_name_long_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //dataset_language
    // The dataset_language field
    $data['metsis_basket']['dataset_language'] = array(
      'title' => t('Dataset language'),
      'help' => t('dataset_language'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_center_contact_role
    // The data_center_contact_role field
    $data['metsis_basket']['data_center_contact_role'] = array(
      'title' => t('Data center contact role'),
      'help' => t('data_center_contact_role'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_access_type
    // The data_access_type field
    $data['metsis_basket']['data_access_type'] = array(
      'title' => t('Data access type'),
      'help' => t('data_access_type'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //project_short_name
    // The project_short_name field
    $data['metsis_basket']['project_short_name'] = array(
      'title' => t('Project short name'),
      'help' => t('project_short_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //abstract
    // The abstract field
    $data['metsis_basket']['abstract'] = array(
      'title' => t('Abstract'),
      'help' => t('abstract'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //activity_type
    // The activity_type field
    $data['metsis_basket']['activity_type'] = array(
      'title' => t('Activity type'),
      'help' => t('activity_type'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //keywords_keyword
    // The keywords_keyword field
    $data['metsis_basket']['keywords_keyword'] = array(
      'title' => t('Keywords keyword'),
      'help' => t('keywords_keyword'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //related_information_type
    // The related_information_type field
    $data['metsis_basket']['related_information_type'] = array(
      'title' => t('Related information type'),
      'help' => t('related_information_type'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_access_wms_layers_wms_layer
    //The data_access_wms_layers_wms_layer field
    $data['metsis_basket']['data_access_wms_layers_wms_layer'] = array(
      'title' => t('Data access wms layers wms layer'),
      'help' => t('data access wms layers wms layer'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //operational_status
    // The operational_status field
    $data['metsis_basket']['operational_status'] = array(
      'title' => t('Operational status'),
      'help' => t('operational_status'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //instrument_long_name
    // The instrument_long_name field
    $data['metsis_basket']['instrument_long_name'] = array(
      'title' => t('Instrument long name'),
      'help' => t('instrument_long_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //personnel_organisation
    // The personnel_organisation field
    $data['metsis_basket']['personnel_organisation'] = array(
      'title' => t('Personnel organisation'),
      'help' => t('personnel_organisation'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_center_contact_email
    // The data_center_contact_email field
    $data['metsis_basket']['data_center_contact_email'] = array(
      'title' => t('Data center contact email'),
      'help' => t('data_center_contact_email'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //instrument_short_name
    // The instrument_short_name field
    $data['metsis_basket']['instrument_short_name'] = array(
      'title' => t('Instrument short name'),
      'help' => t('instrument_short_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //personnel_role
    // The personnel_role field
    $data['metsis_basket']['personnel_role'] = array(
      'title' => t('Personnel role'),
      'help' => t('personnel_role'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_access_description
    // The data_access_description field
    $data['metsis_basket']['data_access_description'] = array(
      'title' => t('Data access description'),
      'help' => t('data_access_description'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //cloud_cover_value
    // the cloud_cover_value field
    $data['metsis_basket']['cloud_cover_value'] = array(
      'title' => t('Cloud cover value'),
      'help' => t('cloud_cover_value'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'iews_handler_field_numeric',
      ),
    );
    //metadata_identifier
    // The metadata_identifier field
    $data['metsis_basket']['metadata_identifier'] = array(
      'title' => t('Metadata identifier'),
      'help' => t('The metadata identifier'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //data_center_data_center_name_short_name
    // The data_center_data_center_name_short_name field
    $data['metsis_basket']['data_center_data_center_name_short_name'] = array(
      'title' => t('Data center data center name short name'),
      'help' => t('data_center_data_center_name_short_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );

    //metadata_status
    // The metadata_status field
    $data['metsis_basket']['metadata_status'] = array(
      'title' => t('Metadata status'),
      'help' => t('metadata_status'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //personnel_name
    // The personnel_name field
    $data['metsis_basket']['personnel_name'] = array(
      'title' => t('Personnel name'),
      'help' => t('personnel_name'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //title
    // The title field
    $data['metsis_basket']['title'] = array(
      'title' => t('Title'),
      'help' => t('title'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );
    //bbox
    // The bbox field
    $data['metsis_basket']['bbox'] = array(
      'title' => t('BBOX'),
      'help' => t('BBOX'),
      'field' => array(
        'handler' => 'views_handler_field',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_string',
      ),
    );

    /**
     * Drupal specific
     */
    // The Node ID field
    $data['metsis_cache']['node_id'] = array(
      'title' => t('Node ID'),
      'help' => t('The record node ID.'),
      'field' => array(
        'handler' => 'views_handler_field_node',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'handler' => 'views_handler_sort',
      ),
      'filter' => array(
        'handler' => 'views_handler_filter_numeric',
      ),
      'relationship' => array(
        'base' => 'node',
        'field' => 'node_id',
        'handler' => 'views_handler_relationship',
        'label' => t('Node'),
      ),
      'argument' => array(
        'handler' => 'views_handler_argument_node_nid',
        'numeric' => TRUE,
        'validate type' => 'nid',
      ),
    );
    return $data;
}

/**
 * metsis_basket_views_data() }
 */
