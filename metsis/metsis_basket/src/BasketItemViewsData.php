<?php

namespace Drupal\metsis_basket;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the metsis_basket_basket_item entity type.
 * {@inheritdoc}
 */
class BasketItemViewsData extends EntityViewsData {

  /**
   * Using standatrd interface to create views data information.
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    return $data;

/**
  * The fields views data can be altered/ovveride here by manipulationg the data array for
  * a specific field before $return_as_object
*/
/*
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
    $data['metsis_basket']['iid'] = array(
      'title' => t('IID'),
      'help' => t('The Item ID.'),
      'field' => array(
        'id' => 'numeric',
      ),
      'sort' => array(
        'sort' => 'standard',),
        'filter' => array(
          'id' => 'numeric',
       ),
    );
    $data['metsis_basket']['uid'] = array(
      'title' => t('UID'),
      'help' => t('The user ID.'),
      'field' => array(
        'id' => 'numeric',
      ),
      'sort' => array(
        'sort' => 'standard',),
        'filter' => array(
          'id' => 'numeric',
        ),
        'argument' => array(
          'id' => 'standard',
          'numeric' => TRUE,
          'validate type' => 'nid',
        ),
      );
    $data['metsis_basket']['user_name'] = array(
      'title' => t('User name'),
      'help' => t('The user name'),
      'field' => array(
        'id' => 'standard',
        'click sortable' => TRUE,
      ),
       'sort' => array(
         'id' => 'standard',
       ),
       'filter' => array(
         'id' => 'string',
       ),
     );
    $data['metsis_basket']['session_id'] = array('title' => t('Session ID'), 'help' => t('The session ID'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['basket_timestamp'] = array(
      'title' => t('Basket timestamp'),
      'help' => t('The timestamp for when item was added to basket'),
      'field' => array(
        'id' => 'date',
        'click sortable' => TRUE,),
        'sort' => array(
          'id' => 'date',
        ),
        'filter' => array(
          'id' => 'date',
        ),
    );
    $data['metsis_basket']['solr_id'] = array('title' => t('SOLR id'), 'help' => t('SOLR id field'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['_version_'] = array('title' => t('SOLR _version_'), 'help' => t('SOLR _version_'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['last_metadata_update'] = array('title' => t('Last metadata update'), 'help' => t('last metadata update'), 'field' => array('handler' => 'views_handler_field_date', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort_date',), 'filter' => array('handler' => 'views_handler_filter_date',),);
    $data['metsis_basket']['personell_email'] = array('title' => t('Personell email'), 'help' => t('The personell_email'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['platform_long_name'] = array('title' => t('Platform long name'), 'help' => t('platform_long_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_center_contact_name'] = array('title' => t('Data center contact name'), 'help' => t('data_center_contact_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['collection'] = array('title' => t('Collection'), 'help' => t('collection'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['geographic_extent_rectangle_east'] = array('title' => t('Geographic extent rectangle east'), 'help' => t('geographic_extent_rectangle_east'), 'field' => array('handler' => 'views_handler_field',), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string', 'click sortable' => TRUE,),);
    $data['metsis_basket']['geographic_extent_rectangle_west'] = array('title' => t('Geographic extent rectangle west'), 'help' => t('geographic_extent_rectangle_west'), 'field' => array('handler' => 'views_handler_field',), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['geographic_extent_rectangle_north'] = array('title' => t('Geographic extent rectangle north'), 'help' => t('geographic_extent_rectangle_north'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['geographic_extent_rectangle_south'] = array('title' => t('Geographic extent rectangle south'), 'help' => t('geographic_extent_rectangle_south'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_center_data_center_url'] = array('title' => t('Data center data center URL'), 'help' => t('data_center_data_center_url'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['platform_short_name'] = array('title' => t('Platform short name'), 'help' => t('platform_short_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['related_information_resource'] = array('title' => t('Related information resource'), 'help' => t('related_information_resource'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['project_long_name'] = array('title' => t('Project long name'), 'help' => t('project_long_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_access_resource_http'] = array('title' => t('Data access resource HTTP'), 'help' => t('data_access_resource_http'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_access_resource_opendap'] = array('title' => t('Data access resource OPenDAP'), 'help' => t('data_access_resource_opendap'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_access_resource_ogc_wms'] = array('title' => t('Data access resource OGC WMS'), 'help' => t('data_access_resource_ogc_wms'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_access_resource_ODATA'] = array('title' => t('Data access resource ODATA'), 'help' => t('data_access_resource_ODATA'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['dataset_production_status'] = array('title' => t('Dataset production status'), 'help' => t('dataset_production_status'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['access_constraint'] = array('title' => t('Access constraint'), 'help' => t('access_constraint'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['iso_topic_category'] = array('title' => t('ISO topic category'), 'help' => t('iso_topic_category'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['temporal_extent_start_date'] = array('title' => t('Temporal extent start date'), 'help' => t('temporal_extent_start_date'), 'field' => array('handler' => 'views_handler_field_date', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort_date',), 'filter' => array('handler' => 'views_handler_filter_date',),);
    $data['metsis_basket']['Temporal extent end date'] = array('title' => t('temporal_extent_end_date'), 'help' => t('temporal_extent_end_date'), 'field' => array('handler' => 'views_handler_field_date', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort_date',), 'filter' => array('handler' => 'views_handler_filter_date',),);
    $data['metsis_basket']['data_center_data_center_name_long_name'] = array('title' => t('Data center data center name long name'), 'help' => t('data_center_data_center_name_long_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['dataset_language'] = array('title' => t('Dataset language'), 'help' => t('dataset_language'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_center_contact_role'] = array('title' => t('Data center contact role'), 'help' => t('data_center_contact_role'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_access_type'] = array('title' => t('Data access type'), 'help' => t('data_access_type'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['project_short_name'] = array('title' => t('Project short name'), 'help' => t('project_short_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['abstract'] = array('title' => t('Abstract'), 'help' => t('abstract'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['activity_type'] = array('title' => t('Activity type'), 'help' => t('activity_type'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['keywords_keyword'] = array('title' => t('Keywords keyword'), 'help' => t('keywords_keyword'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['related_information_type'] = array('title' => t('Related information type'), 'help' => t('related_information_type'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_access_wms_layers_wms_layer'] = array('title' => t('Data access wms layers wms layer'), 'help' => t('data access wms layers wms layer'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['operational_status'] = array('title' => t('Operational status'), 'help' => t('operational_status'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['instrument_long_name'] = array('title' => t('Instrument long name'), 'help' => t('instrument_long_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['personnel_organisation'] = array('title' => t('Personnel organisation'), 'help' => t('personnel_organisation'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_center_contact_email'] = array('title' => t('Data center contact email'), 'help' => t('data_center_contact_email'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['instrument_short_name'] = array('title' => t('Instrument short name'), 'help' => t('instrument_short_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['personnel_role'] = array('title' => t('Personnel role'), 'help' => t('personnel_role'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['data_access_description'] = array('title' => t('Data access description'), 'help' => t('data_access_description'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['cloud_cover_value'] = array('title' => t('Cloud cover value'), 'help' => t('cloud_cover_value'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'iews_handler_field_numeric',),);
    $data['metsis_basket']['metadata_identifier'] = array(
      'title' => t('Metadata identifier'),
      'help' => t('The metadata identifier'),
      'field' => array(
        'id' => 'standard',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'string',
      ),
    );
    $data['metsis_basket']['data_center_data_center_name_short_name'] = array('title' => t('Data center data center name short name'), 'help' => t('data_center_data_center_name_short_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['metadata_status'] = array('title' => t('Metadata status'), 'help' => t('metadata_status'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['personnel_name'] = array('title' => t('Personnel name'), 'help' => t('personnel_name'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['title'] = array('title' => t('Title'), 'help' => t('title'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_basket']['bbox'] = array('title' => t('BBOX'), 'help' => t('BBOX'), 'field' => array('handler' => 'views_handler_field', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_string',),);
    $data['metsis_cache']['node_id'] = array('title' => t('Node ID'), 'help' => t('The record node ID.'), 'field' => array('handler' => 'views_handler_field_node', 'click sortable' => TRUE,), 'sort' => array('handler' => 'views_handler_sort',), 'filter' => array('handler' => 'views_handler_filter_numeric',), 'relationship' => array('base' => 'node', 'field' => 'node_id', 'handler' => 'views_handler_relationship', 'label' => t('Node'),), 'argument' => array('handler' => 'views_handler_argument_node_nid', 'numeric' => TRUE, 'validate type' => 'nid',),);
    return $data;
    */
  }
}
